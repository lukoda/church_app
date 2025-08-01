<?php

namespace App\Filament\Resources\BeneficiaryResource\Pages;

use App\Filament\Resources\BeneficiaryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class CreateBeneficiary extends CreateRecord
{
    protected static string $resource = BeneficiaryResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create Beneficiary')){
            abort_unless(static::getResource()::canCreate(), 403);
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $payment_modes = []; $account_names = []; $account_nos = []; $mobile_account_names = []; $mobile_nos = []; $mobile_account_provider = [];
        $account_provider = [];
        $mobileInstanceCount = 0;
        $bankInstanceCount = 0;
        foreach($data['Beneficiary Donation Details'] as $key => $beneficiary){
            $payment_modes[] = $beneficiary['payment_mode'];
            if($beneficiary['payment_mode'] == 'Mobile'){
                $mobile_account_provider[] = $beneficiary['mobile_account_provider'];
                $mobile_account_names[] = $beneficiary['mobile_account_name'];
                $mobile_nos[] = $beneficiary['mobile_no'];
            }else{
                if($beneficiary['payment_mode'] == 'Bank'){
                    $account_provider[] = $beneficiary['account_provider'];
                    $account_names[] = $beneficiary['account_name'];
                    $account_nos[] = $beneficiary['account_no'];
                }
            }
        }

        $model = static::getModel()::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'group_leader_name' => $data['group_leader_name'] ?? Null,
            'gender' => $data['gender'],
            'phone_no' => $data['phone_no'],
            'frequency' => $data['frequency'],
            'duration' => $data['duration'] ?? Null,
            'status' => 'active',
            'payment_mode' => $payment_modes,
            'account_name' => $account_names ?? Null,
            'account_provider' => $account_provider ?? Null,
            'account_no' => $account_nos  ?? Null,
            'mobile_account_provider' => $mobile_account_provider ?? Null,
            'mobile_account_name' => $mobile_account_names ?? Null,
            'mobile_no' => $mobile_nos ?? Null,
            'church_id' => $data['church_id'],
            'registered_by' => $data['registered_by']
        ]);

        return $model;
    }
}
