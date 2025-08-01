<?php

namespace App\Filament\Resources\ChurchMemberResource\Pages;

use App\Filament\Resources\ChurchMemberResource;
use App\Models\Dependant;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use phpDocumentor\Reflection\Types\Null_;
use App\Models\CardPledge;
use Filament\Notifications\Notification;
use App\Models\ChurchMember;

class EditChurchMember extends EditRecord
{
    protected static string $resource = ChurchMemberResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('update ChurchMember')){
            if(ChurchMember::whereId($this->getRecord()->id)->where('church_id', auth()->user()->church_id)->exists()){
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
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if($data['nida_id'] !== Null || $data['passport_id'] !== Null){
            if($data['nida_id'] == Null){
                $data['identification_type'] = 'passport';
            }else{
                if($data['passport_id'] == Null){
                    $data['identification_type'] = 'nida';
                }
            }
        }

        $data['dependants'] = Dependant::whereChurchMemberId($data['id'])->get();
        return $data;
    }

    protected function afterSave(): void
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

        if(count($this->data['dependants']) > 0){
            if(count($this->record->dependants) > 0){
                $old_ids = [];
                foreach($this->data['dependants'] as $dependant){
                    if(array_key_exists('id', $dependant)){
                        Dependant::whereId($dependant['id'])->update([
                            'first_name' => $dependant['first_name'],
                            'middle_name' => $dependant['middle_name'],
                            'surname' => $dependant['surname'],
                            'date_of_birth' => $dependant['date_of_birth'],
                            'gender' => $dependant['gender'],
                            'relationship' => $dependant['relationship'],
                            'church_member_id' => $this->record->id
                        ]);
                        $old_ids[] = $dependant['id'];
                    }else{
                            $new_dependant = new Dependant;
                            $new_dependant->first_name = $dependant['first_name'];
                            $new_dependant->middle_name = $dependant['middle_name'];
                            $new_dependant->surname = $dependant['surname'];
                            $new_dependant->date_of_birth = $dependant['date_of_birth'];
                            $new_dependant->gender = $dependant['gender'];
                            $new_dependant->relationship = $dependant['relationship'];
                            $new_dependant->church_member_id = $this->record->id;
                            $new_dependant->save();
                            $old_ids[] = $new_dependant->id;
                    }
                }
                Dependant::whereNotIn('id',$old_ids)->where('church_member_id', $this->record->id)->delete();
            }else{
                foreach($this->data['dependants'] as $dependant){
                    $new_dependant = new Dependant;
                    $new_dependant->first_name = $dependant['first_name'];
                    $new_dependant->middle_name = $dependant['middle_name'];
                    $new_dependant->surname = $dependant['surname'];
                    $new_dependant->date_of_birth = $dependant['date_of_birth'];
                    $new_dependant->gender = $dependant['gender'];
                    $new_dependant->relationship = $dependant['relationship'];
                    $new_dependant->church_member_id = $this->record->id;
                    $new_dependant->save();
                }
            }
        }

        if(count($this->data['Card Pledges']) > 0){
            $card_pledge = CardPledge::where('card_no', $this->record->card_no)->where('church_member_id', $this->record->id)->get();
            if(count($card_pledge) > 0){
                foreach($this->data['Card Pledges'] as $key => $pledge){
                    CardPledge::where('card_no', $this->record->card_no)->where('church_member_id', $this->record->id)->where('card_id', $pledge['card_type'])
                    ->where('church_id', auth()->user()->church_id)->update([
                        'amount_pledged' => $pledge['amount_pledged'],
                        'amount_remains' => $pledge['amount_pledged']
                    ]);
                }
            }else{
                foreach($this->data['Card Pledges'] as $pledge){
                    $card_pledge = new CardPledge;
                    $card_pledge->church_member_id = $this->record->id;
                    $card_pledge->card_id = $pledge['card_type'];
                    $card_pledge->card_no = $this->record->card_no ?? Null;
                    $card_pledge->amount_pledged = $pledge['amount_pledged'];
                    $card_pledge->amount_remains = $pledge['amount_pledged'];
                    $card_pledge->amount_completed = 0;
                    $card_pledge->date_pledged = $this->record->created_at;
                    $card_pledge->church_id = auth()->user()->church_id;
                    $card_pledge->created_by = auth()->user()->id;
                    $card_pledge->status = 'Active';
                    $card_pledge->save();
                }
            }
        }
    }
}
