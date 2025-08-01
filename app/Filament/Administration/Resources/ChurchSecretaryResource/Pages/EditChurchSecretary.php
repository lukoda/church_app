<?php

namespace App\Filament\Administration\Resources\ChurchSecretaryResource\Pages;

use App\Filament\Administration\Resources\ChurchSecretaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Models\ChurchMember;
use App\Models\User;
use App\Models\Pastor;
use App\Models\ChurchDistrict;
use App\Models\Church;
use App\Models\ChurchSecretary;
use App\Models\Diocese;

class EditChurchSecretary extends EditRecord
{
    protected static string $resource = ChurchSecretaryResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('update ChurchSecretary')){
            if(auth()->user()->hasRole('Dinomination Admin') && ChurchSecretary::whereId($this->getRecord()->id)->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::whereIn('diocese_id', Diocese::where('dinomination_id', auth()->user()->dinomination_id)->pluck('id'))->pluck('id'))->pluck('id'))->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }elseif(Auth::guard('admin')->user()->hasRole('Parish Admin') && ChurchSecretary::whereId($this->getrecord()->id)->where('church_assigned_id', auth()->user()->church_id)->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(Auth::guard('admin')->user()->hasRole('ChurchDistrict Admin') && ChurchSecretary::whereId($this->getRecord()->id)->where('church_assigned_id', auth()->user()->church_id)->where('title', 'ChurchDistrict Secretary')->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin') && Church::whereId($this->getRecord()->id)->whereIn('church_assigned_id', auth()->user()->church_id)->where('title', 'Bishop Secretary')->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else{
                Notification::make()
                ->title('Page Not Found')
                ->body('Sorry, the requested page does not exist.')
                ->danger()
                ->send();
                redirect()->to(static::getResource()::getUrl('index'));
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $church_member = ChurchMember::whereId($data['church_member_id'])->first();
        $church_district = ChurchDistrict::whereIn('id', Church::whereId($church_member->church_id)->pluck('church_district_id'))->first();
        $user = User::whereId($church_member->user_id)->first();
        $diocese_church = Diocese::whereId($church_district->diocese_id)->first();
        $data['first_name'] = $church_member->first_name;
        $data['middle_name'] = $church_member->middle_name;
        $data['surname'] = $church_member->surname;
        $data['email'] = $church_member->email;
        $data['gender'] = $church_member->gender;
        $data['marital_status'] = $church_member->marital_status;
        $data['phone'] = $church_member->phone;
        $data['diocese'] = $diocese_church->id;
        $data['church_district'] = $church_district->id;    
        $data['church_assigned_id'] = $church_member->church_id;
        $data['church_level'] = $data['title'];

        // if($user->hasRole('Dinomination Admin')){
        //     $data['church_level'] = $data['title'];
        //     $data['diocese'] = $diocese_church->name;
        //     $data['church_district'] = $church_district->id;    
        //     $data['church_assigned_id'] = $church_member->church_id;
        // }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
