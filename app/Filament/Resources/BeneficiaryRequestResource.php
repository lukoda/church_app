<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeneficiaryRequestResource\Pages;
use App\Filament\Resources\BeneficiaryRequestResource\RelationManagers;
use App\Models\BeneficiaryRequest;
use App\Models\Beneficiary;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Awcodes\FilamentTableRepeater\Components\TableRepeater;
use Illuminate\Validation\Rules\Unique;
use Carbon\Carbon;

class BeneficiaryRequestResource extends Resource
{
    protected static ?string $model = BeneficiaryRequest::class;

    protected static ?string $navigationIcon = 'fas-user';

    protected static ?string $navigationGroup = 'Church Administration';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any BeneficiaryRequest');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Select::make('beneficiary_type')
                            ->reactive()
                            ->options([
                                'individual' => 'Individual',
                                'group' => 'Group'
                            ])
                            ->required(),

                        Select::make('beneficiary_id')
                            ->label('Beneficiary Name')
                            ->searchable()
                            ->options(function(Get $get){
                                if($get('beneficiary_type') == 'individual'){
                                    return Beneficiary::where('type', $get('beneficiary_type'))->pluck('name', 'id')->toArray();
                                }else{
                                    if($get('beneficiary_type') == 'group'){
                                        return Beneficiary::where('type', $get('beneficiary_type'))->pluck('name', 'id')->toArray();
                                    }
                                }
                            })
                            ->required()
                            ->unique(modifyRuleUsing: function(Unique $rule, Get $get, $state){
                                return $rule->where('beneficiary_id', $state)
                                            ->where('beneficiary_type', $get('beneficiary_type'))
                                            ->where('status', 'Active');
                            }, ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'This beneficiary already has a pending request'
                            ]),

                        TextInput::make('title')
                        ->required(),
    
                        Hidden::make('church_id')
                            ->default(auth()->user()->church_id),
        
                        TextInput::make('amount')
                            ->label('Amount Requested')
                            ->numeric(),

                        Select::make('request_visible_on')
                            ->label('Request Visible From')
                            ->options([
                                'sunday' => 'Next Sunday Service',
                                'date' => 'specify date'
                            ])
                            ->reactive()
                            ->required(),
        
                        DatePicker::make('begin_date')
                            ->default(now())
                            ->visible(function(Get $get){
                                if($get('request_visible_on') == 'sunday'){
                                    return false;
                                }else{
                                    if($get('request_visible_on') == 'date'){
                                        return true;
                                    }
                                }
                            })
                            ->required(),

                        Select::make('frequency')
                            ->reactive()
                            ->options([
                                'once' => 'once',
                                'days' => 'days',
                                'weeks' => 'weeks',
                                'months' => 'months',
                                'amount' => 'minimum amount'
                            ])
                            ->required(),

                        Textinput::make('weeks')
                            ->visible(function(Get $get){
                                if($get('frequency') == 'weeks'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->numeric()
                            ->required(),

                        Textinput::make('months')
                            ->visible(function(Get $get){
                                if($get('frequency') == 'months'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->numeric()
                            ->required(),

                        DatePicker::make('inactive_on')
                            ->label('end_date')
                            ->visible(function(Get $get){
                                if($get('frequency') == 'days'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        TextInput::make('amount_threshold')
                            ->numeric()
                            ->visible(function(Get $get){
                                if($get('frequency') == 'amount'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        DatePicker::make('end_date')
                            ->visible(function(Get $get){
                                if($get('frequency') == 'amount'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive'
                            ])
                            ->default(fn(string $context) => $context == 'edit' ? '' : 'active')
                            ->visible(function(string $context){
                                if($context == 'edit'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required()
        
                    ]),

                Textarea::make('purpose')
                    ->rows(5)
                    ->columnSpan('full'),

                FileUpload::make('supporting_documents')
                    ->label('Supporting Documents')
                    ->multiple()
                    ->downloadable()
                    ->disk('beneficiaryRequestDocuments')
                    ->columnSpan('full'),

                // Checkbox::make('other_requested_beneficiary_items')
                    
                // TableRepeater::make('Other Beneficiary Requested Items')
                //     ->schema([
                //         TextInput::make('item')
                //             ->required(),

                //         TextInput::make('quantity')
                //             ->numeric()
                //             ->required(),

                //         TextInput::make('description'),


                //     ])
                //     ->visible(function(string $context){
                //         if($context == 'create'){
                //             return true;
                //         }else{
                //             return false;
                //         }
                //     })
                //     ->columnSpanFull(),

                Hidden::make('registered_by')
                        ->default(auth()->user()->id)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                ->searchable(),
                TextColumn::make('purpose')
                    ->limit(50)
                    ->wrap(),
                Textcolumn::make('amount')
                    ->numeric()
                    ->description(function(BeneficiaryRequest $record){
                        if($record->amount_threshold !== Null){
                            return 'Minimum target is '. $record->amount_threshold;
                        }
                    }),
                TextColumn::make('begin_date')
                    ->date()
                    ->searchable(),
                TextColumn::make('end_date')
                    ->date()
                    ->searchable(),
                Textcolumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'active' => 'success',
                        'inactive' => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(! (auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('update BeneficiaryRequest')))
                ->visible(auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('update BeneficiaryRequest')),
                Action::make('deactivate')
                    ->label(fn($record) => $record->status == 'active' ? 'Deactivate' : 'Activate')
                    ->form([
                        TextInput::make('comment'),
                        Checkbox::make('set_request_active_period')
                            ->reactive()
                            ->default(function($record){
                                if($record->end_date > now()){
                                    return false;
                                }else{
                                    return true;
                                }
                            })
                            ->visible(function($record){
                                if($record->status == 'inactive'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->disabled(function($record){
                                if($record->end_date > now()){
                                    return false;
                                }else{
                                    return true;
                                }
                            }),

                        Hidden::make('set_request_active_period')
                            ->default(true)
                            ->disabled(function($record){
                                if($record->end_date > now()){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),
                        
                        Select::make('request_visible_on')
                            ->label('Request Visible From')
                            ->options([
                                'sunday' => 'Next Sunday Service',
                                'date' => 'specify date'
                            ])
                            ->reactive()
                            ->required()
                            ->visible(function(Get $get){
                                if($get('set_request_active_period')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),
        
                        DatePicker::make('begin_date')
                            ->default(now())
                            ->visible(function(Get $get){
                                if($get('request_visible_on') == 'sunday'){
                                    return false;
                                }else{
                                    if($get('request_visible_on') == 'date'){
                                        return true;
                                    }
                                }
                            })
                            ->required(),

                        Select::make('frequency')
                            ->reactive()
                            ->options([
                                'once' => 'once',
                                'days' => 'days',
                                'weeks' => 'weeks',
                                'months' => 'months',
                                'amount' => 'minimum amount'
                            ])
                            ->required()
                            ->visible(function(Get $get){
                                if($get('set_request_active_period')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),

                        Textinput::make('weeks')
                            ->visible(function(Get $get){
                                if($get('frequency') == 'weeks'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->numeric()
                            ->required(),

                        Textinput::make('months')
                            ->visible(function(Get $get){
                                if($get('frequency') == 'months'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->numeric()
                            ->required(),

                        DatePicker::make('inactive_on')
                            ->label('end_date')
                            ->visible(function(Get $get){
                                if($get('frequency') == 'days'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        TextInput::make('amount_threshold')
                            ->numeric()
                            ->visible(function(Get $get){
                                if($get('frequency') == 'amount'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        DatePicker::make('end_date')
                            ->visible(function(Get $get){
                                if($get('frequency') == 'amount'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),
                        
                    ])
                    ->action(function(array $data, $record){
                        if(array_key_exists('set_request_active_period',$data)){
                            if($data['set_request_active_period'] == true){
                                $end_date;
                                if($data['frequency'] == 'weeks'){
                                    if($data['request_visible_on'] == 'sunday'){
                                        $end_date = Carbon::now()->startOfWeek()->subDay()->addWeeks($data['weeks'])->addDays(2);
                                    }else{
                                        $end_date = Carbon::parse($data['begin_date'])->addWeeks($data['weeks'])->addDays(2);
                                    }
                                }else if($data['frequency'] == 'days'){
                                    if($data['request_visible_on'] == 'sunday'){
                                        $end_date = Carbon::now()->startOfWeek()->subDay()->addDays(2);
                                    }else{
                                        $end_date = Carbon::parse($data['inactive_on'])->addDays(2);
                                    }
                                }else if($data['frequency'] == 'months'){
                                    if($data['request_visible_on'] == 'sunday'){
                                        $end_date = Carbon::now()->startOfWeek()->subDay()->addMonths($data['months'])->addDays(2);
                                    }else{
                                        $end_date = Carbon::parse($data['begin_date'])->addMonths($data['months'])->addDays(2);
                                    }
                                }else if($data['frequency'] == 'once'){
                                    if($data['request_visible_on'] == 'sunday'){
                                        $end_date = Carbon::now()->startOfWeek()->subDay()->addDays(8);
                                    }else{
                                        $end_date = Carbon::parse($data['begin_date'])->addDays(2);
                                    }
                                }
                                $record->update([
                                    'request_visible_on' => $data['request_visible_on'],
                                    'frequency' => $data['frequency'],
                                    'begin_date' => $data['request_visible_on'] == 'sunday' ? Carbon::now()->startOfWeek()->subDay()->addWeeks(1) : $data['begin_date'],
                                    'weeks' => $data['weeks'] ?? Null,
                                    'months' => $data['months'] ?? Null,
                                    'inactive_on' => $data['inactive_on'] ?? Null,
                                    'amount_threshold' => $data['amount_threshold'] ?? Null,
                                    'end_date' => $data['frequency'] == 'amount' ? $data['end_date'] : $end_date,
                                ]);
                            }else{
                                if($data['set_request_active_period'] == false && $record->end_date < now()){
                                    $end_date;
                                    if($data['frequency'] == 'weeks'){
                                        if($data['request_visible_on'] == 'sunday'){
                                            $end_date = Carbon::now()->startOfWeek()->subDay()->addWeeks($data['weeks'])->addDays(2);
                                        }else{
                                            $end_date = Carbon::parse($data['begin_date'])->addWeeks($data['weeks'])->addDays(2);
                                        }
                                    }else if($data['frequency'] == 'days'){
                                        if($data['request_visible_on'] == 'sunday'){
                                            $end_date = Carbon::now()->startOfWeek()->subDay()->addDays(2);
                                        }else{
                                            $end_date = Carbon::parse($data['inactive_on'])->addDays(2);
                                        }
                                    }else if($data['frequency'] == 'months'){
                                        if($data['request_visible_on'] == 'sunday'){
                                            $end_date = Carbon::now()->startOfWeek()->subDay()->addMonths($data['months'])->addDays(2);
                                        }else{
                                            $end_date = Carbon::parse($data['begin_date'])->addMonths($data['months'])->addDays(2);
                                        }
                                    }else if($data['frequency'] == 'once'){
                                        if($data['request_visible_on'] == 'sunday'){
                                            $end_date = Carbon::now()->startOfWeek()->subDay()->addDays(8);
                                        }else{
                                            $end_date = Carbon::parse($data['begin_date'])->addDays(2);
                                        }
                                    }
                                    $record->update([
                                        'request_visible_on' => $data['request_visible_on'],
                                        'frequency' => $data['frequency'],
                                        'begin_date' => $data['request_visible_on'] == 'sunday' ? Carbon::now()->startOfWeek()->subDay()->addWeeks(1) : $data['begin_date'],
                                        'weeks' => $data['weeks'] ?? Null,
                                        'months' => $data['months'] ?? Null,
                                        'inactive_on' => $data['inactive_on'] ?? Null,
                                        'amount_threshold' => $data['amount_threshold'] ?? Null,
                                        'end_date' => $data['frequency'] == 'amount' ? $data['end_date'] : $end_date,
                                    ]);
                                }
                            }
                        }
                        if($record->status == 'active'){
                            $record->update([
                                'status' => 'inactive',
                                'comment' => $data['comment']
                            ]);
                        }else{
                            if($record->status == 'inactive'){
                                $record->update([
                                    'status' => 'active',
                                    'comment' => $data['comment']
                                ]);
                            }
                        }
                    })
                    ->visible(auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('deactivate BeneficiaryRequest'))
                    ->disabled(! (auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('deactivate BeneficiaryRequest')))

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
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id)->orderBy('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeneficiaryRequests::route('/'),
            'create' => Pages\CreateBeneficiaryRequest::route('/create'),
            'edit' => Pages\EditBeneficiaryRequest::route('/{record}/edit'),
        ];
    }
}
