<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use App\Models\Church;
use App\Models\ChurchMember;
use App\Models\ChurchMass;
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
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use function PHPUnit\Framework\isNull;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'fas-people-group';

    protected static ?string $navigationGroup = 'Church Administration';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Attendance');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(auth()->user()->id),

                Hidden::make('church_id')
                    ->default(auth()->user()->church_id),
                Grid::make(3)
                    ->schema([
                        Select::make('church_mass_id')
                            ->label('Church Mass')
                            ->options(ChurchMass::all()->where('church_id', auth()->user()->church_id)->pluck('title', 'id'))
                            ->reactive()
                            ->required(),
    
                        DatePicker::make('date')
                            ->readOnly(function(Get $get){
                                if(!blank($get('church_mass_id'))){
                                    $day = ChurchMass::whereId($get('church_mass_id'))->first();
        
                                    if($day->day == 'Sunday'){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                }else{
                                    return true;
                                }
        
                            })
                            ->minDate(function(Get $get) {
                                if(! blank($get('church_mass_id'))){
                                    return Carbon::now()->startOfWeek()->subDay()->addDays(Carbon::parse(ChurchMass::whereId($get('church_mass_id'))->pluck('day')[0])->dayOfWeek)->toDateString(); 
                                }
                            })
                            ->maxDate(function(Get $get) {
                                if(! blank($get('church_mass_id'))){
                                    return Carbon::now()->startOfWeek()->subDay()->addDays(Carbon::parse(ChurchMass::whereId($get('church_mass_id'))->pluck('day')[0])->dayOfWeek)->toDateString(); 
                                }                    
                            })
                            ->required(),
        
                        Placeholder::make('church_mass_date')
                            ->visible(function(Get $get){
                                if(blank($get('church_mass_id'))){
                                    return false;
                                }else{
                                    return true;
                                }
                            })
                            ->content(function (Get $get, Set $set) {
                                if(! blank('church_mass_id')){
                                    $set('date', Carbon::now()->startOfWeek()->subDay()->addDays(Carbon::parse(ChurchMass::whereId($get('church_mass_id'))->pluck('day')[0])->dayOfWeek)->toDateString());
                                    return Carbon::now()->startOfWeek()->subDay()->addDays(Carbon::parse(ChurchMass::whereId($get('church_mass_id'))->pluck('day')[0])->dayOfWeek)->toDateString();
                                }
                            }),
    
                    ]),

                Section::make('Attendances Statistics')
                    ->schema([
                        TextInput::make('men')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('women')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('children')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(3),

                Textarea::make('remark')
                    ->columnSpan('full')
                    ->rows(5)
                    ->default("No Remark"),

                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date(),
                TextColumn::make('church_id')
                    ->label('church')
                    ->formatStateUsing(function($state){
                        return Church::whereId($state)->pluck('name')[0];
                    }),
                TextColumn::make('men')
                    ->numeric(),
                TextColumn::make('women')
                    ->numeric(),
                TextColumn::make('children')
                    ->numeric(),
                TextColumn::make('remark')
                    ->limit(20)
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function(Model $record){
                        $last_entry_date = Carbon::parse($record->date);
                        if(now() < $last_entry_date->addDay(6)->toDateTimeString()){
                            return true;
                        }else{
                            return false;
                        }
                    })
                    ->hidden(! auth()->user()->checkPermissionto('update Attendance')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn (Model $record) => null,
            );
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id)->orderBy('created_at','desc');
    }
}
