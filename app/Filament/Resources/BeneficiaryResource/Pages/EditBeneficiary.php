<?php

namespace App\Filament\Resources\BeneficiaryResource\Pages;

use App\Filament\Resources\BeneficiaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use App\Models\Beneficiary;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use App\Models\User;

class EditBeneficiary extends EditRecord
{
    protected static string $resource = BeneficiaryResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('update Beneficiary')){
            if(Beneficiary::whereId($this->getrecord()->id)->where('church_id', auth()->user()->church_id)->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else{
                Notification::make()
                ->title('Page Not Found')
                ->body('Sorry, the requested page does not exist.')
                ->danger()
                ->send();
            }
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }

    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->visible(auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('delete Beneficiary')),
            Actions\Action::make('edit_payment_details')
                ->fillForm(function (Beneficiary $record): array {
                    $items = [];
                    $mobileInstanceCount = 0;
                    $bankInstanceCount = 0;
                    foreach($record->payment_mode as $key => $mode){       
                        $items[] = [
                            'payment_mode' => $mode,
                            'account_provider' => $record->account_provider == Null ? '' : $record->account_provider[$bankInstanceCount] ?? Null,
                            'account_name' => $record->account_name == Null ? '' : $record->account_name[$bankInstanceCount] ?? Null,
                            'account_no' => $record->account_no == Null ? '' : $record->account_no[$bankInstanceCount]?? NUll,
                            'mobile_account_no' => $record->mobile_account_no == Null ? '' : $record->mobile_accont_no[$mobileInstanceCount] ?? Null,
                            'mobile_account_name' => $record->mobile_account_name == Null ? '' : $record->mobile_account_name[$mobileInstanceCount] ?? Null,
                            'mobile_account_provider' => $record->mobile_account_provider == Null ? '' : $record->mobile_account_provider[$mobileInstanceCount] ?? Null,
                            'mobile_no' => $record->mobile_no == Null ? '' : $record->mobile_no[$mobileInstanceCount] ?? Null,
                        ];

                        if($mode == 'Mobile'){
                            $mobileInstanceCount++;
                        }else if($mode == 'Bank'){
                            $bankInstanceCount++;
                        }
                    }
                    return [
                        'Edit Beneficiary Donation Details' => $items
                    ];
                })
                ->form([
                    Repeater::make('Edit Beneficiary Donation Details')
                        ->columns(4)
                        ->collapsible()
                        ->columnSpan('full')
                        ->schema([
                            Select::make('payment_mode')
                                ->reactive()
                                ->options([
                                    'Mobile' => 'Mobile',
                                    'Bank' => 'Bank'
                                ])
                                ->required(),

                            Select::make('account_provider')
                                ->reactive()
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
                                ->required()
                                ->afterStateUpdated(function(Set $set){
                                    $set('account_name', '');
                                    $set('account_no', '');
                                }),

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
                                ->reactive()
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
                                ->required()
                                ->afterStateUpdated(function(Set $set){
                                    $set('mobile_account_name', '');
                                    $set('mobile_no', '');
                                }),

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
                                ->tel()
                                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[\s\.\/0-9]*$/')
                                ->helperText('+255*********')
                                ->maxLength(13)
                                ->visible(function(Get $get){
                                    if($get('payment_mode') == 'Mobile'){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                })
                                ->required(),
                         ])->live(onBlur:true)
                   
                ])
                ->action(function(array $data, $record){
                    $payment_modes = []; $account_names = []; $account_nos = []; $mobile_account_names = []; $mobile_nos = [];
                    $account_providers = []; $mobile_account_providers = [];
                    foreach($data['Edit Beneficiary Donation Details'] as $key => $beneficiary){
                        $payment_modes[] = $beneficiary['payment_mode'];
                        if($beneficiary['payment_mode'] == 'Mobile'){
                            $mobile_account_providers[] = $beneficiary['mobile_account_provider'];
                            $mobile_account_names[] = $beneficiary['mobile_account_name'];
                            $mobile_nos[] = $beneficiary['mobile_no'];
                        }else{
                            if($beneficiary['payment_mode'] == 'Bank'){
                                $account_providers[] = $beneficiary['account_provider'];
                                $account_names[] = $beneficiary['account_name'];
                                $account_nos[] = $beneficiary['account_no'];
                            }
                        }
                    }

                    $record->update([
                        'payment_mode' => $payment_modes,
                        'account_name' => $account_names ?? Null,
                        'account_no' => $account_nos  ?? Null,
                        'account_provider' => $account_providers ?? Null,
                        'mobile_account_provider' => $mobile_account_providers ?? Null,
                        'mobile_account_name' => $mobile_account_names ?? Null,
                        'mobile_no' => $mobile_nos ?? Null,
                    ]);

                    Notification::make()
                        ->title('Beneficiary Donation details updated successfully')
                        ->success()
                        ->send();
                })
                ->visible(auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('update Beneficiary'))
        ];
    }
}
