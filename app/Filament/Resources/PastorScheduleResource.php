<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PastorScheduleResource\Pages;
use App\Filament\Resources\PastorScheduleResource\RelationManagers;
use App\Models\ChurchMember;
use App\Models\Pastor;
use App\Models\PastorSchedule;
use Attribute;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Facades\DB;

class PastorScheduleResource extends Resource
{
    protected static ?string $model = PastorSchedule::class;

    protected static ?string $navigationIcon = 'fas-calendar-days';

    protected static ?string $navigationGroup = 'Church Administration';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any PastorSchedule');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('frequency')
                    ->options([
                        'once' => 'Specific Date',
                        'week' => 'This Week',
                    ])
                    ->required()
                    ->reactive(),

                    Select::make('pastor_id')
                        ->label('Pastor')
                        ->options(
                            DB::table('pastors')
                                ->join('church_members','church_members.id', '=', 'pastors.church_member_id')
                                ->where('church_assigned_id', auth()->user()->church_id)
                                ->pluck('full_name', 'pastors.id')
                        )
                        ->required()
                        ->hidden(auth()->user()->hasRole(['Senior Pastor', 'Pastor', 'ChurchDistrict Pastor', 'Diocese Bishop']) ? true : false),

                    TextInput::make('pastor')
                        ->default(function(){
                            return DB::table('pastors')
                                        ->join('church_members','church_members.id', '=', 'pastors.church_member_id')
                                        ->where('church_assigned_id', auth()->user()->church_id)
                                        ->pluck('full_name')[0];
                        })
                        ->readOnly()
                        ->hidden(auth()->user()->hasRole(['Senior Pastor', 'Pastor', 'ChurchDistrict Pastor', 'Diocese Bishop']) ? true : false),

                    Hidden::make('pastor')
                        ->default(function(){
                            return DB::table('pastors')
                                        ->join('church_members','church_members.id', '=', 'pastors.church_member_id')
                                        ->where('church_assigned_id', auth()->user()->church_id)
                                        ->pluck('pastors.id')[0];
                        })
                        ->hidden(auth()->user()->hasRole(['Senior Pastor', 'Pastor', 'ChurchDistrict Pastor', 'Diocese Bishop']) ? true : false),

                Repeater::make('schedule')
                    ->relationship('schedules')
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
                                if($get('../../frequency') == 'week'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(function(Get $get){
                                if($get('../../frequency') == 'once'){
                                    return false;
                                }else{
                                    if($get('../../frequency') == 'week'){
                                        return true;
                                    }
                                }
                            }),

                            
                    DatePicker::make('day')
                        ->native(false)
                        ->suffixIcon('heroicon-o-calendar')
                        ->minDate(now()->subDay())
                        ->required(function(Get $get){
                            if($get('../../frequency') == 'once'){
                                return true;
                            }else{
                                if($get('../../frequency') == 'week'){
                                    return false;
                                }
                            }
                        })
                        ->visible(function(Get $get){
                            if($get('../../frequency') == 'once'){
                                return true;
                            }else{
                                return false;
                            }
                        }),

                        TimePicker::make('from')
                            ->prefix('from')
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('to')
                            ->prefix('to')
                            ->seconds(false)
                            ->required(),

                        Hidden::make('status')
                            ->default(true),

                        Toggle::make('status')
                            ->label('available')
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false)
                            ->visible(function(string $context){
                                if($context == 'edit'){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),

                        TextInput::make('max_members')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)


                    ])
                    ->columns(4)
                    ->minItems(1)
                    ->columnSpan('full'),


                Hidden::make('church_id')
                    ->default(auth()->user()->church_id)

            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day')
                    ->date(),
                TextColumn::make('schedules.from')
                    ->time('H:i A')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->bulleted(),
                TextColumn::make('schedules.to')
                    ->time('H:i A')
                    ->listWithLineBreaks()
                    ->limitList(3),
                TextColumn::make('schedules.status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '' => 'warning',
                        '1' => 'success',
                    })
                    ->formatStateUsing(function($state){
                        if($state == '1'){
                            return 'available';
                        }else{
                            return 'unavailable';
                        }
                    })
                    ->listWithLineBreaks()
                    ->limitList(3),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(! (auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('update PastorSchedule')))
                ->visible(auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('update PastorSchedule')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(! (auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('update PastorSchedule')))
                    // ->visible(auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('update PastorSchedule')),
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
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPastorSchedules::route('/'),
            'create' => Pages\CreatePastorSchedule::route('/create'),
            'edit' => Pages\EditPastorSchedule::route('/{record}/edit'),
        ];
    }
}
