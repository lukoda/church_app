<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use App\Models\PastorSchedule;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Carbon\Carbon;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'fas-calendar-check';

    protected static ?string $navigationGroup = 'Church Services';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Booking');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('pastor_schedule_id')
                    ->label('Pastor Schedules')
                    ->options(PastorSchedule::all()->pluck('day', 'id')->toArray())
                    ->searchable()
                    ->required()
                    ->reactive(),

                Section::make('Available Time')
                    ->schema([
                        Select::make('day_of_week')
                            ->options([
                                'Monday' => 'Monday',
                                'Tuesday' => 'Tuesday',
                                'Wednesday' => 'Wednesday',
                                'Thursday' => 'Thursday',
                                'Friday' => 'Friday',
                                'Saturday' => 'Saturday',
                                'Sunday' => 'Sunday'
                            ])
                            ->visible(function(Get $get){
                                if(PastorSchedule::whereId('pastor_schedule_id')->where('frequency','week')->count() > 0){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        Select::make('from')
                            ->options(function(Get $get, Set $set) {
                                if(PastorSchedule::whereId('pastor_schedule_id')->where('frequency','week')->count() > 0){
                                    return Schedule::where('pastor_schedule_id', $get('pastor_schedule_id'))->where('day_of_week', $get('day_of_week'))->pluck('from', 'id');
                                }else{
                                    return Schedule::where('pastor_schedule_id', $get('pastor_schedule_id'))->pluck('from', 'id');
                                }
                            })
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function(Set $set, $state, Get $get){
                                $set('to', Schedule::whereId($state)->pluck('to')[0]);
                                $set('booked_schedule', $state);
                            }),

                        Timepicker::make('to'),
                    ])
                    ->description('Please choose time slot before booking')
                    ->columns(2)
                    ->columnSpan('full'),

                Hidden::make('booked_schedule'),

                Hidden::make('approval_status')
                    ->default(0),

                Hidden::make('church_member_id')
                    ->default(auth()->user()->churchMember->id),

                Hidden::make('church_id')
                    ->default(auth()->user()->church_id)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pastor_schedule_id')
                    ->label('Date')
                    ->formatStateUsing(function($state){
                        return PastorSchedule::whereId($state)->pluck('day');
                    })
                    ->visible(function($record){
                        if($record != Null){
                            if(Schedule::where('pastor_schedule_id', $record->pastor_schedule_id)->whereNotNull('day_of_week')->count() > 0){
                                return false;
                            }else{
                                return true;
                            }
                        }else{
                            return false;
                        }

                    })
                    ->date(),

                TextColumn::make('pastor_schedule_id')
                    ->label('Day')
                    ->formatStateUsing(function($state, $record){
                        return Schedule::where('pastor_schedule_id', $record->pastor_schedule_id)->pluck('day_of_week');
                    })
                    ->visible(function($record){
                        if($record != Null){
                            if(Schedule::where('pastor_schedule_id', $record->pastor_schedule_id)->whereNotNull('day_of_week')->count() > 0){
                                return true;
                            }else{
                                return false;
                            }
                        }else{
                            return false;
                        }

                    }),

                TextColumn::make('booked_schedule')
                    ->label('Time Slot Booked')
                    ->formatStateUsing(function($state){
                        return Schedule::whereId($state)->pluck('from')[0].' - '.Schedule::whereId($state)->pluck('to')[0];
                    }),

                TextColumn::make('approval_status')
                    ->label('Approval Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        '1' => 'success',
                        '0' => 'danger'
                    })
                    ->formatStateUsing(function($state){
                        if($state == '1'){
                            return 'approved';
                        }else{
                            return 'not approved';
                        }
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->visible(auth()->user()->checkPermissionTo('update Booking')),
                Action::make('approve')
                    ->fillForm(fn (Booking $record): array => [
                        'current_booked' => Booking::where('booked_schedule', $record->booked_schedule)->where('approval_status', '1')->count()
                    ])
                    ->form([
                        TextInput::make('current_booked')
                            ->disabled(),
                        Toggle::make('approval_status'),        
                    ])
                    ->action(function(array $data, Booking $record){
                        $record->update([
                            'approval_status' => $data['approval_status']
                        ]);
                        $schedules = Schedule::whereid($record->booked_schedule)->first();
                        $schedules->current_booked_members += 1;
                        $schedules->save();
                    })
                    ->visible(auth()->user()->checkPermissionTo('approve Booking'))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if(auth()->user()->hasRole(['Senior Pastor', 'Pastor', 'Church Secretary', 'SubParish Pastor', 'ChurchDistrict Pastor', 'Diocese Bishop'])){
            return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id);
        }else if(auth()->user()->hasRole('Church Member')){
            return parent::getEloquentQuery()->where('church_member_id', auth()->user()->churchMember->id);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
