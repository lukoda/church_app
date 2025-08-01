<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PastorResource\Pages;
use App\Filament\Resources\PastorResource\RelationManagers;
use App\Models\Pastor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\Church;
use App\Models\ChurchDistrict;
use App\Models\Diocese;
use Filament\Forms\Get;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

class PastorResource extends Resource
{
    protected static ?string $model = Pastor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Pastor');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)
                    ->schema([
                        TextInput::make('first_name')
                        ->required(),

                        TextInput::make('middle_name')
                            ->required(),

                        TextInput::make('surname')
                            ->required(),

                        TextInput::make('email')
                            ->nullable()
                            ->email(),

                        Select::make('gender')
                            ->options([
                                'Male' => 'Male',
                                'Female' => 'Female'
                            ])
                            ->required(),

                        TextInput::make('phone')
                            ->tel()
                            ->helperText('0789*********')
                            ->maxLength(10)
                            ->required(),

                        Select::make('church_level')
                        ->reactive()
                        ->options([
                            'Bishop' => 'Diocese',
                            'ArchBishop' => 'Dinomination'
                        ])
                        ->required()
                        ->visible(auth()->user()->hasRole('Dinomination Admin')),

                        Select::make('diocese')
                        ->live(onBlur: true)
                        ->options(Diocese::all()->pluck('name', 'id'))
                        ->required()
                        ->visible(auth()->user()->hasRole('Dinomination Admin')),

                        Select::make('church_district')
                        ->live(onBlur: true)
                        ->options(function (Get $get) {
                            if(blank($get('diocese'))){
                                return [];
                            }else{
                                return ChurchDistrict::whereIn('diocese_id', $get('diocese'))->pluck('name', 'id');
                            }
                        })
                        ->required()
                        ->visible(auth()->user()->hasRole('Dinomination Admin')),

                        Select::make('church_assigned_id')
                            ->label('Assigned Church')
                            ->options(function(Get $get){
                                if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                    return Church::where('church_district_id', auth()->user()->church->churchDistrict->id)->pluck('name', 'id');
                                }else if(auth()->user()->hasRole('Dinomination Admin')){
                                    if($get('diocese') && $get('church_district')){
                                        return Church::where('church_district_id', $get('church_district'))->pluck('name', 'id');
                                    }else{
                                        return [];
                                    }
                                }else if(auth()->user()->hasRole('Parish Admin')){
                                    return Church::where('parent_church', auth()->user()->church_id)->where('church_type', 'sub_parish')->pluck('name', 'id');
                                }
                            }),

                        Select::make('title')
                        ->options(function(Get $get){
                            if(auth()->user()->hasRole('ChurchDistrict Admin')){
                                $church_district_churches = Church::where('church_district_id', auth()->user()->church->churchDistrict->id)->pluck('id');
                                if(Pastor::whereIn('church_assigned_id', $church_district_churches)->where('title', 'ChurchDistrict Pastor')->where('status', 'active')->exists()){
                                    return [
                                        'Senior Pastor' => 'Senior Pastor',
                                        'Pastor' => 'Pastor'
                                    ];
                                }else{
                                    return [
                                        'ChurchDistrict Pastor' => 'ChurchDistrict Pastor',
                                        'Senior Pastor' => 'Senior Pastor',
                                        'Pastor' => 'Pastor'
                                    ];
                                }
                            }else if(auth()->user()->hasRole('Dinomination Admin')){
                                if($get('church_level') == 'Dinomination'){
                                    if(Pastor::whereIn('church_assigned_id', Church::all()->pluck('id'))->where('title', $get('church_level'))->exists()){
                                        return [];
                                    }else{
                                        return [
                                            $get('church_level') => $get('church_level')
                                        ];
                                    }
                                }else if($get('church_level') == 'Diocese'){
                                    $diocese_church_districts = ChurchDistrict::where('diocese_id', $get('diocese'))->pluck('id');
                                    $diocese_churches = Church::whereIn('church_district_id', $diocese_church_districts)->pluck('id');
                                    if(Pastor::whereIn('church_assigned_id', $diocese_churches)->where('title', $get('church_level'))->exists()){
                                        return [];
                                    }else{
                                        return [
                                            $get('church_level') => $get('church_level')
                                        ];
                                    }
                                }
                            }else if(auth()->user()->hasRole('Parish Admin')){
                                return [
                                    'SubParish Pastor' => 'SubParish Pastor'
                                ];
                            }
                        })

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_registered')
                ->date(),
                TextColumn::make('church_assigned_id')
                ->formatStateUsing(fn($state) => Church::whereId($state)->pluck('name')[0]),
                TextColumn::make('title'),
                TextColumn::make('churchMember.full_name')
                ->wrap(),
                TextColumn::make('churchMember.phone')
                ->wrap(),
                TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'deceased' => 'warning',
                    'retired' => 'gray',
                })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('update Pastor'))
                ->visible(auth()->user()->checkPermissionTo('update Pastor')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(! auth()->user()->checkPermissionTo('delete Pastor'))
                    // ->visible(auth()->user()->checkPermissionTo('delete Pastor')),
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
        if(auth()->user()->hasRole(['ArchBishop', 'Dinomination Admin'])){
            return parent::getEloquentQuery()->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::whereIn('diocese_id', Diocese::where('dinomination_id', auth()->user()->dinomination_id)->pluck('id'))->pluck('id'))->pluck('id'))->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole('Bishop')){
            return parent::getEloquentQuery()->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->church->churchDistrict->diocese->id)->pluck('id'))->pluck('id'))->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole(['ChurchDistrict Pastor', 'ChurchDistrict Admin'])){
            return parent::getEloquentQuery()->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::whereId(auth()->user()->church->churchDistrict->id)->pluck('id'))->pluck('id'))->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole(['Senior Pastor', 'Pastor', 'Parish Admin'])){
            return parent::getEloquentQuery()->whereIn('church_assigned_id', Church::where('parent_church', auth()->user()->church_id)->pluck('id'))->orderBy('created_at', 'desc');
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPastors::route('/'),
            'create' => Pages\CreatePastor::route('/create'),
            'edit' => Pages\EditPastor::route('/{record}/edit'),
        ];
    }
}
