<?php

namespace App\Filament\Administration\Resources;

use App\Filament\Administration\Resources\AdminResource\Pages;
use App\Filament\Administration\Resources\AdminResource\RelationManagers;
use App\Models\Admin;
use App\Models\Dinomination;
use App\Models\Diocese;
use App\Models\ChurchDistrict;
use App\Models\Church;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return "Church Admins";
    }

    public static function getNavigationLabel(): string
    {
        return "Church Admins";
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('admin')->user()->hasRole('Parish Admin') ? false : true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('phone')
                    ->tel()
                    ->helperText('0789*********')
                    ->maxLength(10)
                    ->required()
                    ->unique(),

                TextInput::make('password')
                ->password()
                ->required(),

                Hidden::make('church_level')
                ->default(function(){
                    if(auth()->user()->hasRole('Dinomination Admin')){
                        return 'Diocese';
                    }else if(auth()->user()->hasRole('Diocese Admin')){
                        return 'ChurchDistrict';
                    }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
                        return 'Parish';
                    }
                }),

                Select::make('diocese_id')
                ->label('Diocese')
                ->options(function(string $context, $record){
                    if($context == 'edit'){
                        $diocese_admin = Admin::whereId($record->id)->pluck('diocese_id');
                        return Diocese::whereNotIn('id', $diocese_admin)->where('dinomination_id', auth()->user()->dinomination_id)->pluck('name', 'id');
                    }else{
                        $diocese_admin = Admin::whereNotNull('diocese_id')->pluck('diocese_id');
                        return Diocese::whereNotIn('id', $diocese_admin)->where('dinomination_id', auth()->user()->dinomination_id)->pluck('name', 'id');
                    }
                })
                ->required()
                ->visible(auth()->user()->hasRole('Dinomination Admin')),

                Select::make('church_district_id')
                ->label('Church District')
                ->options(function(string $context, $record){
                    if($context == 'edit'){
                        $district_admin = Admin::whereId($record->id)->pluck('church_district_id');
                        return ChurchDistrict::whereNotIn('id', $district_admin)->where('diocese_id', auth()->user()->diocese_id)->pluck('name', 'id');
                    }else{
                        $district_admin = Admin::whereNotNull('church_district_id')->pluck('church_district_id');
                        return ChurchDistrict::whereNotIn('id', $district_admin)->where('diocese_id', auth()->user()->diocese_id)->pluck('name', 'id');
                    }
                })
                ->required()
                ->visible(auth()->user()->hasRole('Diocese Admin')),

                Select::make('church_id')
                ->label('Church')
                ->options(function(string $context, $record){
                    if($context == 'edit'){
                        $church_admin = Admin::whereId($record->id)->pluck('church_id');
                        return Church::whereNotIn('id', $church_admin)->where('church_district_id', auth()->user()->church_district_id)->pluck('name', 'id');
                    }else{
                        $church_admin = Admin::whereNotNull('church_id')->pluck('church_id');
                        return Church::whereNotIn('id', $church_admin)->where('church_district_id', auth()->user()->church_district_id)->pluck('name', 'id');
                    }
                })
                ->required()
                ->visible(auth()->user()->hasRole('ChurchDistrict Admin')),

                Hidden::make('dinomination_id')
                ->default(auth()->user()->dinomination_id)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone')
                ->searchable(),
                TextColumn::make('church_level'),
                // TextColumn::make('role')
                // ->default(function($record){
                //     if($record->church_level == 'Diocese'){
                //         return 'Diocese Admin';
                //     }else if($record->church_level == 'ChurchDistrict'){
                //         return 'ChurchDistrict Admin';
                //     }else if($record->church_level == 'Parish'){
                //         return 'Parish Admin';
                //     }
                // }),
                // TextColumn::make('diocese.name')
                // ->label('Assigned Diocese')
                // ->searchable()
                // ->default('-'),
                TextColumn::make('churchDistrict.name')
                ->searchable()
                ->label('Assigned ChurchDistrict')
                ->default('-'),
                TextColumn::make('church.name')
                ->searchable()
                ->label('Assigned Church')
                ->default('-'),
                // TextColumn::make('dinomination_id')
                // ->label('Assigned Dinomination')
                // ->formatStateUsing(fn(string $state) => Dinomination::whereId($state)->pluck('name')[0])
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
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
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if(auth()->user()->hasRole('Dinomination Admin')){
            return parent::getEloquentQuery()->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole('Diocese Admin')){
            return parent::getEloquentQuery()->whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->orderBy('created_at', 'desc');
        }else if(auth()->user()->hasRole('ChurchDistrict Admin')){
            return parent::getEloquentQuery()->whereIn('church_id', Church::where('church_district_id',  auth()->user()->church_district_id)->pluck('id'))->orderBy('created_at', 'desc');
        }
    }
}
