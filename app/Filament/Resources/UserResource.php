<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use App\Models\Dinomination;
use App\Models\Church;
use App\Models\Diocese;
use App\Models\Region;
use App\Models\District;
use App\Models\UserRole;
use App\Models\Ward;
use Filament\Forms\Get;
use Filament\Tables\Columns\TextColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'fas-people-group';

    protected static ?string $navigationGroup = 'Settings';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any User');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('phone')
                    ->label(__('Phone Number'))
                    ->placeholder('+255*********')
                    ->tel()
                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[\s\.\/0-9]*$/')
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1]),

                TextInput::make('password')
                    ->password()
                    ->minLength(8)
                    ->required(),

                Hidden::make('dinomination_id')
                ->default(auth()->user()->dinomination_id),

                Hidden::make('church_id')
                ->default(auth()->user()->church_id),

                // Select::make('dinomination_id')
                //     ->label('Dinomination')
                //     ->options(Dinomination::all()->pluck('name', 'id'))
                //     ->required(),

                // Select::make('church_id')
                //     ->searchable()
                //     ->label('church')
                //     // ->options(function (Get $get){
                //     //     return Church::all()->pluck('name', 'id')->toArray();
                //     // })
                //     ->getSearchResultsUsing(function(string $search, Get $get): array{
                //         return Church::orWhereIn('region_id', Region::where('name', 'like', $search)->pluck('id'))
                //                         ->orWhereIn('district_id', District::where('name', 'like', $search)->pluck('id'))
                //                         ->orWhereIn('ward_id', Ward::where('name', 'like', $search)->pluck('id'))
                //                         ->orWhere('name', 'like', $search)->pluck('name','id')->toArray();
                //     })
                //     ->getOptionLabelsUsing(function(array $values): array {
                //         return Church::whereIn('id', $values)->pluck('name','id')->toarray();
                //     })->required(),

                Select::make('roles')->multiple()->relationship('roles', 'name')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone'),
                TextColumn::make('dinomination_id')
                    ->label('Dinomination')
                    ->formatStateUsing(fn($state) => Dinomination::whereId($state)->pluck('name')[0]),
                Textcolumn::make('church_id')
                    ->label('Church')
                    ->formatStateUsing(fn($state) => Church::whereId($state)->pluck('name')[0]),
                TextColumn::make('assigned_role')
                    ->default(function($record){
                        return Role::whereIn('id',UserRole::where('user_id', $record->id)->pluck('role_id'))->pluck('name');
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('update User'))
                ->visible(auth()->user()->checkPermissionTo('update User')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(! auth()->user()->checkPermissionTo('delete User'))
                    // ->visible(auth()->user()->checkPermissionTo('delete User')),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
