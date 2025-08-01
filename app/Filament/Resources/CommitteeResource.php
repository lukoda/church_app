<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommitteeResource\Pages;
use App\Filament\Resources\CommitteeResource\RelationManagers;
use App\Models\ChurchMember;
use App\Models\Committee;
use App\Models\Jumuiya;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
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
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommitteeResource extends Resource
{
    protected static ?string $model = Committee::class;

    protected static ?string $navigationIcon = 'fas-people-group';

    protected static ?string $modelLabel = 'Church Elder';

    protected static ?string $navigationGroup = 'Church Administration';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Committee');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('church_id')
                    ->default(auth()->user()->church_id),

                Grid::make(3)
                    ->schema([
                        Select::make('church_member_id')
                            ->label('Church Member')
                            ->searchable()
                            ->reactive()
                            ->getSearchResultsUsing(function(string $search): array {
                                $committee = DB::table('committees')->where('end_date', '<', now())->selectRaw(DB::raw('Count(*) as terms, church_member_id'))->groupBy('church_member_id')->having('terms','>=',2)->get();
                                $church_member_id = [];
                                foreach($committee as $terms){
                                    $church_member_id[] = $terms->church_member_id;
                                }

                                $eligible_members = ChurchMember::whereNotIn('id',$church_member_id);
                                return $eligible_members->where('card_no', 'like', "%{$search}%")
                                                        ->orWhere('surname', 'like', "%{$search}%")
                                                        ->pluck('surname','id')->toArray();
                            })
                            ->getOptionLabelUsing(fn($value): string => ChurchMember::find('value')->full_name)
                            ->helperText('Search by surname or card no')
                            ->afterStateUpdated(function($state, Set $set){
                                $set('jumuiya', Jumuiya::whereId(ChurchMember::whereId($state)->pluck('jumuiya_id'))->where('church_id', auth()->user()->church_id)->pluck('name')[0]);
                            }),

                        TextInput::make('jumuiya')
                            ->readOnly()
                            ->visible(function(Get $get){
                                if($get('church_member_id')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),

                        DatePicker::make('begin_date')
                            ->default(now())
                            ->required(),

                        DatePicker::make('end_date')
                            ->reactive()
                            ->default(fn(Get $get) => blank($get('begin_date')) ? now()->addYears(4) : Carbon::parse($get('begin_date'))->addYears(4))
                            ->minDate(now()->addYears(4))
                            ->maxDate(now()->addYears(4)->addMonths(1))
                            ->required()
                            ->afterStateUpdated(function(Get $get, $state, Set $set){
                                if(! blank($state)){
                                    $set('serve_duration', Carbon::parse($state)->diffInYears($get('begin_date')));
                                }
                            }),

                        TextInput::make('serve_duration')
                            ->readOnly()
                            ->numeric()
                            ->prefix('Years'),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive'
                            ])
                            ->default(function(string $context){
                                if($context == 'create'){
                                    return 'Active';
                                }
                            })
                            ->visible(function(string $context){
                                if($context == 'edit'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->reactive()
                            ->required(),

                        Hidden::make('status')
                            ->default('Active'),

                        TextInput::make('comment')
                            ->required()
                            ->visible(function(Get $get){
                                if($get('status') == 'Inactive'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('church_member_id')
                    ->label('Church Member')
                    ->formatStateUsing(function($state){
                        $full_name = ChurchMember::whereId($state)->first();
                        return "{$full_name->first_name} {$full_name->middle_name} {$full_name->surname}";
                    }),

                TextColumn::make('jumuiya')
                    ->default(function(Model $record){
                        return Jumuiya::whereId(ChurchMember::whereId($record->church_member_id)->pluck('jumuiya_id'))->pluck('name')[0];
                    }),

                TextColumn::make('begin_date')
                    ->date()
                    ->description(function(Model $record, $state){
                        return 'Time remain in Office '.Carbon::parse($record->end_date)->diffInYears($state).' years';
                    }),

                TextColumn::make('end_date')
                    ->date(),

                TextColumn::make('serve_duration')
                    ->numeric(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'Active' => 'success',
                        'Inactive' => 'danger'
                    }),

                TextColumn::make('comment')
                    ->default(function(Model $record){
                        if($record->comment == Null){
                            return "No Comment";
                        }else{
                            return $record->comment;
                        }
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('update Committee'))
                ->visible(auth()->user()->checkPermissionTo('update Committee')),
                Tables\Actions\DeleteAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('delete Committee'))
                ->visible(auth()->user()->checkPermissionTo('delete Committee')),
                Action::make('Deactivate')
                    ->label(fn($record) => $record->status == 'Active' ? 'Deactivate' : 'Activate')
                    ->form([
                        TextInput::make('comment')
                            ->required(fn($record) => $record->status == 'Activate' ? true : false),
                    ])
                    ->action(function(array $data, $record){
                        $record->update([
                            'comment' => $data['comment'] ?? $record->comment,
                            'status' => $record->status == 'Inactive' ? 'Active' : 'Inactive'
                        ]);
                    })
                    ->disabled(! auth()->user()->checkPermissionto('deactivate Committee'))
                    ->visible(auth()->user()->checkPermissionto('deactivate Committee'))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(! auth()->user()->checkPermissionTo('delete Committee'))
                    // ->visible(auth()->user()->checkPermissionTo('delete Committee')),
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
            'index' => Pages\ManageCommittees::route('/'),
        ];
    }

}
