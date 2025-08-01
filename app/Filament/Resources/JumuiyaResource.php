<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JumuiyaResource\Pages;
use App\Filament\Resources\JumuiyaResource\RelationManagers;
use App\Models\Church;
use App\Models\Jumuiya;
use App\Models\Ward;
use App\Models\Region;
use App\Models\District;
use App\Models\ChurchDistrict;
use App\Models\Diocese;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class JumuiyaResource extends Resource
{
    protected static ?string $model = Jumuiya::class;

    protected static ?string $navigationIcon = 'fas-people-roof';

    protected static ?string $navigationGroup = 'Church Administration';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Jumuiya');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Hidden::make('church_id')
                        ->default(auth()->user()->church_id),
        
                        TextInput::make('name')
                            ->unique(modifyRuleUsing: function(Unique $rule,$record, $state, Get $get){
                                return $rule->where('name', $state)
                                            ->where('church_id', $get('church_id'));
                            },ignoreRecord: true)
                            ->label('Jumuiya Name')
                            ->required(),

                            Select::make('region')
                                ->options(function(){
                                        $churchdistrict = Church::whereId(auth()->user()->church_id)->first();
                                        $churchdistrict = ChurchDistrict::whereId($churchdistrict->id)->first();
                                        $diocese = Diocese::whereId($churchdistrict->diocese_id)->pluck('regions');
                                        return Region::whereIn('name', $diocese->flatten())->pluck('name','name')->toArray();
                                })
                                ->distinct()
                                ->reactive()
                                ->required()
                                ->afterStateUpdated(function(Set $set){
                                    $set('districts', []);
                                }),

                        Select::make('district')
                            ->reactive()
                            ->options(function(Get $get){
                                if(blank($get('region'))){
                                    return [];
                                }else{
                                    $churchdistrict = Church::whereId(auth()->user()->church_id)->first();
                                    $churchdistrict = ChurchDistrict::whereId($churchdistrict->id)->first();
                                    $diocese = Diocese::whereId($churchdistrict->diocese_id)->pluck('districts');

                                    return District::whereIn('id', $diocese->flatten())->where('region_id', Region::whereName($get('region'))->pluck('id'))->pluck('name', 'name');
                                    // return District::whereIn('id', Diocese::whereId($get('../../diocese_id'))->pluck('districts'))->pluck('name', 'id');
                                    // return $churchdistrict->keys();
                                }
                            })
                            ->visible(function(Get $get){
                                if($get('all_districts')){
                                    return false;
                                }else{
                                    return true;
                                }
                            }),
        
                        Select::make('ward')
                            ->searchable()
                            ->label('Jumuiya Location(ward)')
                            ->options(function(Get $get){
                                // return Ward::whereDistrictId(Church::whereId(auth()->user()->church_id)->pluck('district_id'))->pluck('name','name');
                                if(blank($get('district'))){
                                    return [];
                                }else{
                                    return Ward::where('district_id', District::where('name', $get('district'))->pluck('id'))->pluck('name','name');
                                }
                            })
                            ->reactive()
                            ->required(),

                        TextInput::make('street')
                            ->visible(function(Get $get){
                                if($get('ward')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),

                        TextInput::make('postal_code'),
        
                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive'
                            ])
                            ->default('Active'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('church_id')
                    ->label('Church')
                    ->formatStateUsing(function($state){
                        return Church::whereId($state)->pluck('name')[0];
                    }),
                TextColumn::make('name'),
                TextColumn::make('jumuiya_members')
                    ->formatStateusing(function($record){
                        return $record->jumuiya_members->count();
                    }),
                TextColumn::make('ward')
                    ->label('ward')
                    ->description(fn($record): string => $record->street != Null ? "street covered is ".$record->street : '' ),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'Active' => 'success',
                        'Inactive' => 'danger'
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('update Jumuiya'))
                ->visible(auth()->user()->checkPermissionTo('update Jumuiya')),
                Tables\Actions\DeleteAction::make()
                    ->action(function(Tables\Actions\DeleteAction $action, Model $record){
                        if($record->jumuiya_members->count() > 0){
                            Notification::make()
                                ->warning()
                                ->title('Jumuiya has members')
                                ->body('Can\'t delete jumuiya has members')
                                ->persistent()
                                ->send();   
                        }
                        $action->cancel();
                    })
                    ->disabled(! auth()->user()->checkPermissionTo('delete Jumuiya'))
                    ->visible(auth()->user()->checkPermissionTo('delete Jumuiya')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(! auth()->user()->checkPermissionTo('delete Jumuiya'))
                    // ->visible(auth()->user()->checkPermissionTo('delete Jumuiya')),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageJumuiyas::route('/'),
        ];
    }
}
