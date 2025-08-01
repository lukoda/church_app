<?php

namespace App\Filament\Administration\Resources\PastorResource\Pages;

use App\Filament\Administration\Resources\PastorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\ChurchMember;
use App\Models\Church;
use App\Models\User;
use App\Models\Diocese;
use App\Models\ChurchDistrict;
use Filament\Notifications\Notification;
use App\Models\Pastor;
use Illuminate\Support\Facades\Auth;

class EditPastor extends EditRecord
{
    protected static string $resource = PastorResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('update Pastor')){
            if(auth()->user()->hasRole('Dinomination Admin') && Pastor::whereId($this->getRecord()->id)->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::whereIn('diocese_id', Diocese::where('dinomination_id', auth()->user()->dinomination_id)->pluck('id'))->pluck('id'))->pluck('id'))->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin') && Pastor::whereId($this->getRecord()->id)->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->pluck('id'))->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(Auth::guard('admin')->user()->hasRole('ChurchDistrict Admin') && Pastor::whereId($this->getRecord()->id)->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::whereId(auth()->user()->church_district_id)->pluck('id'))->pluck('id'))->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(Auth::guard('admin')->user()->hasRole('Parish Admin') && Pastor::whereId($this->getRecord()->id)->whereIn('church_assigned_id', Church::where('parent_church', auth()->user()->church_id)->pluck('id'))->exists()){
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
        $data['church_level'] = $data['title'];
        $data['diocese'] = $diocese_church->id;
        $data['church_district'] = $church_district->id;    
        $data['church_assigned_id'] = $church_member->church_id;
        // if($user->hasRole('Dinomination Admin')){
        //     $data['church_level'] = $data['title'];
        //     $data['diocese'] = $diocese_church->id;
        //     $data['church_district'] = $church_district->id;    
        //     $data['church_assigned_id'] = $church_member->church_id;
        // }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->disabled(! auth()->user()->checkPermissionTo('delete Pastor'))
            ->visible(auth()->user()->checkPermissionTo('delete Pastor')),
        ];
    }
}
