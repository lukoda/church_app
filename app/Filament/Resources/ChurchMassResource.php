<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChurchMassResource\Pages;
use App\Filament\Resources\ChurchMassResource\RelationManagers;
use App\Models\ChurchMass;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use League\CommonMark\Input\MarkdownInput;
use Symfony\Component\HttpKernel\Fragment\HIncludeFragmentRenderer;
use Illuminate\Validation\Rules\Unique;

class ChurchMassResource extends Resource
{
    protected static ?string $model = ChurchMass::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Church Administration';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any ChurchMass');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('day')
                ->options([
                    'Monday' => 'Monday',
                    'Tuesday' => 'Tuesday',
                    'Wednesday' => 'Wednesday',
                    'Thursday' => 'Thursday',
                    'Friday' => 'Friday',
                    'Saturday' => 'Saturday',
                    'Sunday' => 'Sunday'
                ])
                ->reactive()
                ->required(),

                TextInput::make('title')
                    ->unique(modifyRuleUsing: function(Unique $rule, string $state, Get $get){
                        return $rule->where('title', $state)
                                    ->where('church_id', auth()->user()->church_id)
                                    ->where('day', $get('day'));
                    }, ignoreRecord: true)
                    ->required(),



                TimePicker::make('start_time')
                    ->seconds(false)
                    ->required(),

                TimePicker::make('end_time')
                    ->seconds(false)
                    ->required(),

                Select::make('frequency')
                ->options([
                        'Always' => 'Always',
                        // 'Weekly' => 'Weekly',
                    ])
                    ->default('Always'),

                RichEditor::make('description')
                    ->columnSpan('full'),

                Hidden::make('church_id')
                    ->default(auth()->user()->church_id)
            ])
        ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('day'),
                TextColumn::make('start_time')
                    ->time(),
                TextColumn::make('end_time'),
                TextColumn::make('frequency')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->visible(auth()->user()->checkPermissionTo('view ChurchMass')),
                Tables\Actions\EditAction::make()
                ->visible(auth()->user()->checkPermissionTo('update ChurchMass')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->visible(auth()->user()->checkPermissionTo('delete ChurchMass')),
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
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id)->orderBy('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChurchMasses::route('/'),
            'create' => Pages\CreateChurchMass::route('/create'),
            'view' => Pages\ViewChurchMass::route('/{record}'),
            'edit' => Pages\EditChurchMass::route('/{record}/edit'),
        ];
    }
}
