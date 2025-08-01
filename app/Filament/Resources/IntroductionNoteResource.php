<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IntroductionNoteResource\Pages;
use App\Filament\Resources\IntroductionNoteResource\RelationManagers;
use App\Models\IntroductionNote;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Church;
use App\Models\ChurchMember;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Unique;
use Filament\Actions\StaticAction;


class IntroductionNoteResource extends Resource
{
    protected static ?string $model = IntroductionNote::class;

    protected static ?string $navigationIcon = 'fas-person-chalkboard';

    protected static ?string $navigationGroup = 'Church Member Requests';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any IntroductionNote');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('from_church_id')
                    ->default(auth()->user()->church_id),

                Hidden::make('church_member_id')
                    ->default(auth()->user()->churchMember->id),
                    
                Grid::make(3)
                    ->schema([
    
                        Select::make('title')
                            ->label('Purpose')
                            ->options([
                                'Travel' => 'Travel',
                                'Holiday' => 'Holiday',
                                'Work' => 'Work',
                                'Family' => 'Family',
                                'Other' => 'Other'
                            ])
                            ->required(),
        
                        DatePicker::make('date_requested')
                            ->label('Date Of Leave')
                            ->default(now())
                            ->minDate(now())
                            ->required(),

                        TextInput::make('sundays_on_leave')
                            ->numeric()
                            ->required()
                    ]),
                
                RichEditor::make('description')
                        ->label('Message')
                        ->disableToolbarButtons([
                            'codeBlock',
                            'strike',
                            'link',
                            'attachFiles'
                        ])
                        ->columnSpan('full'),

                Section::make('Destination details')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('region_id')
                            ->preload()
                            ->reactive()
                            ->searchable()
                            ->label('Region')
                            ->options(Region::pluck('name', 'id')->toArray())
                            ->afterStateUpdated(function (Set $set): void {
                                $set('district_id', null);
                                $set('ward_id', null);
                            }),

                        Select::make('district_id')
                            ->preload()
                            ->searchable()
                            ->label('District')
                            ->options(function (Get $get) {
                                if (blank($get('region_id'))) {
                                    return [];
                                }

                                $region = Region::whereId($get('region_id'))->first();

                                return $region->districts()->pluck('name', 'id')->toArray();
                            })
                            ->reactive(),

                        Select::make('ward_id')
                            ->preload()
                            ->searchable()
                            ->label('Ward')
                            ->options(function (Get $get) {
                                if (blank($get('district_id'))) {
                                    return [];
                                }

                                $district = District::whereId($get('district_id'))->first();

                                return $district->wards()->pluck('name', 'id')->toArray();
                            }),

                        Select::make('to_church_id')
                            ->label('To Church')
                            ->options(function(Get $get){
                                if(blank($get('region_id')) && blank($get('district_id')) && blank($get('ward_id'))){
                                    return [];
                                }else{
                                    if(blank($get('ward_id'))){
                                        return Church::where('region_id', $get('region_id'))->where('district_id', $get('district_id'))->pluck('name', 'id');
                                    }else{
                                        return Church::where('region_id', $get('region_id'))->where('district_id', $get('district_id'))->where('ward_id', $get('ward_id'))->pluck('name', 'id');
                                    }                               
                                }
                            })
                            ->searchable()
                        ]),

                        Hidden::make('status')
                            ->default('active')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('church_member_id')
                ->label('Requested By')
                ->formatStateUsing(fn($state) => ChurchMember::whereId($state)->pluck('full_name')[0]),
                TextColumn::make('title')
                ->searchable(),
                TextColumn::make('date_requested')
                    ->date(),
                TextColumn::make('address')
                    ->label('Destination Address')
                    ->default(function($record){
                        $region = Region::whereId($record->region_id)->pluck('name');
                        $district = District::whereId($record->district_id)->pluck('name');
                        $ward = Ward::whereId($record->ward_id)->pluck('name');
                        if($record->region_id == Null){
                            return 'No address specified';
                        }else{
                            if($record->district_id == Null){
                                return "{$region[0]}";
                            }else if($record->ward_id == Null){
                                return "{$district[0]}, {$region[0]}";
                            }else{
                                return "{$ward[0]}, {$district[0]} {$region[0]}";
                            }
                        }
                    }),
                TextColumn::make('from_church_id')
                    ->label('From Church')
                    ->formatStateUsing(function($state){
                        return Church::whereId($state)->pluck('name')[0];
                    }),
                TextColumn::make('to_church_id')
                    ->label('To Church')
                    ->formatStateUsing(function($state){
                        if(blank($state)){
                            return '-';
                        }else{
                            return Church::whereId($state)->pluck('name')[0];
                        }
                    }),   
                TextColumn::make('approval_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'Pending Approval' => 'warning',
                        'Conditional Approval' => 'warning',
                        'Not Approved' => 'danger',
                        'Approved' => 'success'
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->hidden(true),
                Action::make('approve')
                    ->label(function(IntroductionNote $record){
                        if($record->approval_status == 'Approved'){
                            return 'Approved';
                        }else{
                            return 'Approve';
                        }
                    })
                    ->fillForm(fn(IntroductionNote $record): array => [
                        'to_church_id' => Church::whereId($record->to_church_id)->pluck('name'),
                        'title' => $record->title,
                        'date_requested' => $record->date_requested,
                        'description' => $record->description,
                        'address' => Ward::whereId($record->region_id)->pluck('name')[0].', '.District::whereId($record->district_id)->pluck('name')[0].' '.Region::whereId($record->region_id)->pluck('name')[0],
                        'approval_status' => $record->approval_status,
                        'approved_on' => $record->approved_on
                    ])
                    ->form([
                        // Step::make('Introduction Note Details')
                        //     ->description('Details Of Note Request')
                        //     ->schema([
                            Grid::make(3)
                            ->schema([
                                TextInput::make('to_church_id')
                                ->label('Destination')
                                ->disabled(),

                                TextInput::make('title')
                                ->label('Purpose')
                                ->disabled(),

                                DatePicker::make('date_requested')
                                    ->suffixIcon('heroicon-o-calendar')
                                    ->disabled(),

                                RichEditor::make('description')
                                    ->disableToolbarButtons([
                                        'codeBlock',
                                        'strike',
                                        'link',
                                        'attachFiles'
                                    ])
                                    ->disabled()
                                    ->columnSpan('full'),

                                TextInput::make('address')
                                    ->disabled(),

                                Textarea::make('leaving_note')
                                    ->label('Word Of Farewell')
                                    ->rows(5)
                                    ->nullable()
                                    ->columnSpanFull()
                            ])
                            // ])
                            // ->columns(3),

                            // Step::make('Approval Details')
                            //             ->schema([
                            //                 Select::make('approval_status')
                            //                 ->options([
                            //                     'Approved' => 'Approve',
                            //                     'NotApproved' => 'Disapprove'
                            //                 ])
                            //                 ->required(),

                            //                 DatePicker::make('approved_on')
                            //                     ->minDate(now())
                            //                     ->default(now()),

                            //                 Textarea::make('leaving_note')
                            //                     ->label('Word Of Farewell')
                            //                     ->rows(5)
                            //                     ->nullable()
                            //                     ->columnSpanFull()
                            //             ])
                            //             ->columns(2)
                    ])
                    ->action(function(array $data, IntroductionNote $record, array $arguments){
                        if($arguments['status'] == true){
                            $record->approved_on = now();
                            $record->leaving_note = $data['leaving_note'] ?? Null;
                            $record->approval_status = $record->to_church_id !== Null ? 'Approved' :'Conditional Approval';
                            $record->save();
                            Notification::make()
                                ->title('Successfully Sent')
                                ->body('Intoduction Note successfully approved')
                                ->success()
                                ->send();
                        }else if($arguments['status'] == false){
                            $record->approved_on = now();
                            $record->leaving_note = $data['leaving_note'] ?? Null;
                            $record->approval_status = $record->to_church_id !== Null ? 'Not Approved' :'Not Approved';
                            $record->save();
                            Notification::make()
                                ->title('Successfully Sent')
                                ->body('Introduction Note successfully disapproved.')
                                ->success()
                                ->send();
                        }
                    })
                    ->disabled(function($record){
                        if($record->approval_status == 'Approved'){
                                return true;
                        }else{
                            if($record->approval_status == 'Not Approved'){
                                if(! auth()->user()->hasRole('Church Secretary')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }
                        }
                    })
                    ->visible(function($record){
                        // if($record->to_church_id == Null){
                        //     return false;
                        // }else{
                            if(auth()->user()->checkPermissionTo('approve IntroductionNote')){
                                return true;
                            }else{
                                return false;
                            }
                        // }
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalFooterActions(fn (StaticAction $action): array => [
                        $action->makeModalSubmitAction('Approve', arguments: ['status' => true])->color('success'),
                        $action->makeModalSubmitAction('Disapprove', arguments: ['status' => false])->color('danger')
                    ]),

                // Action::make('confirm_destination')
                //     ->label(fn($record) => $record->to_church_id != Null ? ($record->approval_status != Null ? $record->approval_status : 'Pending Approval') : 'Confirm Destination')
                //     ->fillForm(fn(IntroductionNote $record): array => [
                //         'region_id' => $record->region_id,
                //         'district_id' => $record->district_id,
                //         'ward_id' => $record->ward_id
                //     ])
                //     ->form([
                //             Grid::make(4)
                //                 ->schema([
                //                     Select::make('region_id')
                //                     ->preload()
                //                     ->reactive()
                //                     ->searchable()
                //                     ->label('Region')
                //                     ->options(Region::pluck('name', 'id')->toArray())
                //                     ->afterStateUpdated(function (Set $set): void {
                //                         $set('district_id', null);
                //                         $set('ward_id', null);
                //                     })
                //                     ->required(),

                //                     Select::make('district_id')
                //                         ->preload()
                //                         ->searchable()
                //                         ->label('District')
                //                         ->options(function (Get $get) {
                //                             if (blank($get('region_id'))) {
                //                                 return [];
                //                             }

                //                             $region = Region::whereId($get('region_id'))->first();

                //                             return $region->districts()->pluck('name', 'id')->toArray();
                //                         })
                //                         ->reactive()
                //                         ->required(),

                //                     Select::make('ward_id')
                //                         ->preload()
                //                         ->searchable()
                //                         ->label('Ward')
                //                         ->options(function (Get $get) {
                //                             if (blank($get('district_id'))) {
                //                                 return [];
                //                             }

                //                             $district = District::whereId($get('district_id'))->first();

                //                             return $district->wards()->pluck('name', 'id')->toArray();
                //                         }),

                //                     Select::make('to_church_id')
                //                         ->label('To Church')
                //                         ->options(function(Get $get){
                //                             if(blank($get('region_id')) && blank($get('district_id')) && blank($get('ward_id'))){
                //                                 return [];
                //                             }else{
                //                                 if(blank($get('ward_id'))){
                //                                     return Church::where('region_id', $get('region_id'))->where('district_id', $get('district_id'))->pluck('name', 'id');
                //                                 }else{
                //                                     return Church::where('region_id', $get('region_id'))->where('district_id', $get('district_id'))->where('ward_id', $get('ward_id'))->pluck('name', 'id');
                //                                 }
                //                             }
                //                         })
                //                         ->searchable()
                //                         ->required()
                //                 ])
                //             ])
                //             ->action(function(array $data, IntroductionNote $record){
                //                 $record->region_id = $data['region_id'];
                //                 $record->district_id = $data['district_id'];
                //                 $record->ward_id = $data['ward_id'];
                //                 $record->to_church_id = $data['to_church_id'];
                //                 $record->status = $record->to_church_id !== Null ? ($record->approval_status == 'Approved' ? 'Approved' : 'Conditional Approval') :'Conditional Approval';
                //                 $record->save();

                //                 Notification::make()
                //                     ->title('Approval request sent')
                //                     ->success()
                //                     ->send();
                //             })
                //             ->visible(function($record){
                //                 if($record->to_church_id !== Null){
                //                     return false;
                //                 }else{
                //                     return true;
                //                 }
                //             })
                //             ->disabled(function($record){
                //                 if($record->to_church_id == Null){
                //                     return false;
                //                 }else{
                //                     return true;
                //                 }
                //             })
                //             ->hidden(! auth()->user()->hasRole('Church Secretary')),



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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntroductionNotes::route('/'),
            'create' => Pages\CreateIntroductionNote::route('/create'),
            'edit' => Pages\EditIntroductionNote::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', '!=', 'inactive')->where('from_church_id', auth()->user()->church_id)->orderBy('date_requested', 'asc');
    }
}
