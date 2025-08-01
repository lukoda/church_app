<?php

namespace App\Filament\Resources\BeneficiaryRequestResource\Pages;

use App\Filament\Resources\BeneficiaryRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\BeneficiaryRequestItem;
use App\Models\Beneficiary;
use App\Models\User;
use Filament\Notifications\Notification;

class CreateBeneficiaryRequest extends CreateRecord
{
    protected static string $resource = BeneficiaryRequestResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('create BeneficiaryRequest')){
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

        $model = static::getModel()::create([
            'title' => $data['title'],
            'beneficiary_type' => $data['beneficiary_type'],
            'beneficiary_id' => $data['beneficiary_id'],
            'church_id' => $data['church_id'],
            'amount' => $data['amount'],
            'request_visible_on' => $data['request_visible_on'],
            'begin_date' => $data['request_visible_on'] == 'sunday' ? Carbon::now()->startOfWeek()->subDay()->addWeeks(1) : $data['begin_date'],
            'frequency' => $data['frequency'],
            'weeks' => $data['weeks'] ?? Null,
            'months' => $data['months'] ?? Null,
            'inactive_on' => $data['inactive_on'] ?? Null,
            'amount_threshold' => $data['amount_threshold'] ?? Null,
            'status' => 'Active',
            'purpose' => $data['purpose'],
            'supporting_documents' => $data['supporting_documents'],
            'end_date' => $data['frequency'] == 'amount' ? $data['end_date'] : $end_date,
            'registered_by' => $data['registered_by']
        ]);

        if(! User::where('phone', Beneficiary::whereId($data['beneficiary_id'])->pluck('phone_no')[0])->exists()){
            $user = new User;
            $user->phone = Beneficiary::whereId($data['beneficiary_id'])->pluck('phone_no')[0];
            $user->password = $data['beneficiary_type'] == 'group' ? strtoupper(str_replace(' ', '', Beneficiary::whereId($data['beneficiary_id'])->pluck('group_leader_name')[0])) : strtoupper(str_replace(' ', '', Beneficiary::whereId($data['beneficiary_id'])->pluck('name')[0]));
            $user->church_id = auth()->user()->church_id;
            $user->dinomination_id = auth()->user()->dinomination_id;
            $user->save();
    
            $user->assignRole('Beneficiary');
        }else if(User::where('phone', Beneficiary::whereId($data['beneficiary_id'])->pluck('phone_no')[0])->exists()){
            $user = User::where('phone', Beneficiary::whereId($data['beneficiary_id'])->pluck('phone_no')[0])->first();
            $user->phone = Beneficiary::whereId($data['beneficiary_id'])->pluck('phone_no')[0];
            $user->password = $data['beneficiary_type'] == 'group' ? strtoupper(str_replace(' ', '', Beneficiary::whereId($data['beneficiary_id'])->pluck('group_leader_name')[0])) : strtoupper(str_replace(' ', '', Beneficiary::whereId($data['beneficiary_id'])->pluck('name')[0]));
            $user->save();

            $user->assignRole('Beneficiary');
        }

        return $model;
    }

    // protected function afterCreate(): void
    // {
    //     foreach($this->data['Other Beneficiary Requested Items'] as $item){
    //         $beneficiaryItems = new BeneficiaryRequestItem;
    //         $beneficiaryItems->item = $item['item'];
    //         $beneficiaryItems->quantity = $item['quantity'];
    //         $beneficiaryItems->description = $item['description'];
    //         $beneficiaryItems->beneficiary_request_id = $this->record->id;
    //         $beneficiaryItems->save();
    //     }
    // }
}
