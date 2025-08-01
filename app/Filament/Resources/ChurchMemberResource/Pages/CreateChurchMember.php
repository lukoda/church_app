<?php

namespace App\Filament\Resources\ChurchMemberResource\Pages;

use App\Filament\Resources\ChurchMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\CardPledge;
use App\Models\Card;
use App\Models\Dependant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class CreateChurchMember extends CreateRecord
{
    protected static string $resource = ChurchMemberResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create ChurchMember')){
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        if($this->record->first_name !== Null && $this->record->region_id !== Null && $this->record->received_confirmation !== Null){
            $this->record->update([
                'personal_details' => 'complete',
                'address_details' => 'complete',
                'spiritual_information' => 'complete'
            ]);
        }else{
            if($this->record->first_name !== Null){
                $this->record->update([
                    'personal_details' => 'complete'
                ]);
            }else{
                if($this->record->region_id !== Null){
                    $this->record->update([
                        'address_details' => 'complete'
                    ]);
                }else{
                    if($this->record->received_confirmation !== Null){
                        $this->record->update([
                            'spiritual_information' => 'complete'
                        ]);
                    }
                }
            }
        }
        if(count($this->data['Card Pledges']) > 0){

            foreach($this->data['Card Pledges'] as $pledge){
                if($pledge['card_type'] != Null && $pledge['amount_pledged'] != Null){
                    $card_pledge = new CardPledge;
                    $card_pledge->church_member_id = $this->record->id;
                    $card_pledge->card_id = $pledge['card_type'];
                    $card_pledge->card_no = $this->record->card_no ?? Null;
                    $card_pledge->amount_pledged = $pledge['amount_pledged'];
                    $card_pledge->amount_remains = 0;
                    $card_pledge->amount_completed = 0;
                    $card_pledge->date_pledged = $this->record->created_at;
                    $card_pledge->created_by = auth()->user()->id;
                    $card_pledge->church_id = auth()->user()->church_id;
                    $card_pledge->status = 'Active';
                    $card_pledge->save();
                }
            }
        }

        if(count($this->data['dependants']) > 0){
            foreach($this->data['dependants'] as $dependant){
                $member_dependants = new Dependant;
                $member_dependants->first_name = $dependant['first_name'];
                $member_dependants->middle_name = $dependant['middle_name'];
                $member_dependants->surname = $dependant['surname'];
                $member_dependants->gender = $dependant['gender'];
                $member_dependants->date_of_birth = $dependant['date_of_birth'];
                $member_dependants->relationship = $dependant['relationship'];
                $member_dependants->church_member_id = $this->record->id;
                $member_dependants->save();
            }
        }


        // if(! User::wherePhone($this->record->phone)->exists()){
        //     $user = new User;
        //     $user->phone = $this->record->phone;
        //     $user->password = Hash::make('1234');
        //     $user->save();

        //     $this->record->update([
        //         'user_id' => $user->id
        //     ]);
        // }

    }
}
