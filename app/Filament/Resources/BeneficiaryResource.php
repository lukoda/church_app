<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeneficiaryResource\Pages;
use App\Filament\Resources\BeneficiaryResource\RelationManagers;
use App\Models\Beneficiary;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\Rules\Unique;

class BeneficiaryResource extends Resource
{
    protected static ?string $model = Beneficiary::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Church Beneficiaries';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any Beneficiary');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Select::make('type')
                            ->label('Beneficiary Type')
                            ->options([
                                'individual' => 'Individual',
                                'group' => 'Group'
                            ])
                            ->reactive()
                            ->required(),
        
                        TextInput::make('name')
                            ->label('Beneficiary Name')
                            ->unique(modifyRuleUsing: function(Unique $rule, string $state, Get $get){
                                    return $rule->where('type', $get('type'))
                                                ->where('name', $state);
                            }, ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'The beneficiary has already been registered.',
                            ])
                            ->required(),
        
                        TextInput::make('group_leader_name')
                            ->label('Contact Person Name')
                            ->visible(function(Get $get){
                                if($get('type') == 'group'){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),
        
                        Select::make('gender')
                            ->options([
                                'Male' => 'Male',
                                'Female' => 'Female'
                            ])
                            ->required(),
        
                        TextInput::make('phone_no')
                            ->unique()
                            ->tel()
                            ->helperText('0789******')
                            ->maxLength(10)
                            ->required()
                            ->validationMessages([
                                'unique' => 'The user with this number already exists.',
                            ]),

                        Select::make('frequency')
                            ->options(function(Get $get){
                                if($get('type') == 'individual'){
                                    return [
                                        'once' => 'once'
                                    ];
                                }else{
                                    if($get('type') == 'group'){
                                        return [
                                            'once' => 'once',
                                            'permanent' => 'permanent'
                                        ];
                                    }
                                }
                            })
                            ->default('once')
                            ->reactive()
                            ->required(),

                        //hidden field update
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive'
                            ])
                            ->visible(function(string $context){
                                if($context == 'edit'){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),
                    ]),

                Repeater::make('Beneficiary Donation Details')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('payment_mode')
                            ->reactive()
                            ->options([
                                'Mobile' => 'Mobile',
                                'Bank' => 'Bank'
                            ])
                            ->required(),

                        Select::make('account_provider')
                            ->options([
                                'crdb' => 'CRDB',
                                'nmb' => 'NMB',
                                'maendeleo' => 'MAENDELEO BANK'
                            ])
                            ->visible(function(Get $get){
                                if($get('payment_mode') == 'Bank'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        TextInput::make('account_name')
                            ->visible(function(Get $get){
                                if($get('payment_mode') == 'Bank'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        TextInput::make('account_no')
                            ->visible(function(Get $get){
                                if($get('payment_mode') == 'Bank'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        Select::make('mobile_account_provider')
                            ->options([
                                'm-pesa' => 'MPESA',
                                'tigopesa' => 'TIGOPESA',
                                'halopesa' => 'HALOPESA',
                                'airtelmoney' => 'AIRTELMONEY',
                                'ttclpesa' => 'TTCLPESA',
                                'ezypesa' => 'EZYPESA'
                            ])
                            ->visible(function(Get $get){
                                if($get('payment_mode') == 'Mobile'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        TextInput::make('mobile_account_name')
                            ->visible(function(Get $get){
                                if($get('payment_mode') == 'Mobile'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        TextInput::make('mobile_no')
                            ->label('Mobile No/Lipa Namba')
                            ->tel()
                            ->helperText('0789******')
                            ->maxLength(10)
                            ->maxLength(13)
                            ->visible(function(Get $get){
                                if($get('payment_mode') == 'Mobile'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),

                        ])
                        ->visible(function(string $context){
                            if($context == 'create'){
                                return true;
                            }else{
                                return false;
                            }
                        }),

                        Hidden::make('church_id')
                            ->default(auth()->user()->church_id),

                        Hidden::make('registered_by')
                            ->default(auth()->user()->id)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                ->searchable(),
                TextColumn::make('name')
                    ->description(function(Beneficiary $record){
                        if($record->group_leader_name !== Null){
                            return "Group Leader name is ".$record->group_leader_name;
                        }
                    })
                    ->searchable(),
                TextColumn::make('phone_no')
                ->searchable(),
                TextColumn::make('frequency')
                    ->description(function(Beneficiary $record){
                        if($record->frequency == 'temporary'){
                            return 'Active for '.$record->duration.' weeks';
                        }
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'active' => 'success',
                        'inactive' => 'danger'
                    })
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->visible(auth()->user()->checkPermissionTo('update Beneficiary')),
                Tables\Actions\Action::make('deactivate')
                    ->label(fn($record) => $record->status == 'Active' ? 'Deactivate' : 'Activate')
                    ->form([
                        Checkbox::make('deactivate_beneficiary_requests')
                            ->label(fn($record) => $record->status == 'Active' ? 'Deactivate Beneficiary Requests' : 'Activate Beneficiary Requests')
                            ->inline(false)
                            ->default(true)
                            ->required(),
                    ])
                    ->action(function(array $data, $record){
                        if($record->status == 'active'){
                            $record->update([
                                'status' => 'inactive'
                            ]);
                            if(array_key_exists('deactivate_beneficiary_requests', $data)){
                                if($data['deactivate_beneficiary_requests'] == true){
                                    $record->beneficiary_requests->each(function($request){
                                       return $request->update([
                                            'status' => 'inactive'
                                        ]);
                                    });
                                }
                            }
                        }else{
                            if($record->status == 'inactive'){
                                $record->update([
                                    'status' => 'active'
                                ]);
                            }
                            if(array_key_exists('deactivate_beneficiary_requests', $data)){
                                if($data['deactivate_beneficiary_requests'] == true){
                                    $record->beneficiary_requests->each(function($request){
                                       return $request->where('end_date', '>=', now())->update([
                                            'status' => 'active'
                                        ]);
                                    });
                                }
                            }
                        }
                    })
                    ->visible(auth()->user()->checkPermissionTo('deactivate Beneficiary'))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->visible(auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('delete Beneficiary')),
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
            'index' => Pages\ListBeneficiaries::route('/'),
            'create' => Pages\CreateBeneficiary::route('/create'),
            'edit' => Pages\EditBeneficiary::route('/{record}/edit'),
        ];
    }
}
