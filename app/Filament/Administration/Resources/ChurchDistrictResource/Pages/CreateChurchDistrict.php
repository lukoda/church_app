<?php

namespace App\Filament\Administration\Resources\ChurchDistrictResource\Pages;

use App\Filament\Administration\Resources\ChurchDistrictResource;
use App\Models\Ward;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\Church;
use App\Models\ChurchDistrict;

class CreateChurchDistrict extends CreateRecord
{
    protected static string $resource = ChurchDistrictResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('create ChurchDistrict')){
            if(auth()->user()->hasRole('Dinomination Admin') && Church::where('church_type', 'dinomination')->exists()){
                Notification::make()
                ->title('Can not create more than one Dinomination Church District')
                ->body('Each Dinomination can have only one Dinomination Church DIstrict.')
                ->danger()
                ->send();
                redirect()->to(static::getResource()::getUrl('index'));
            }else if(auth()->user()->hasRole('Dinomination Admin') && ChurchDistrict::count() > 0){
                Notification::make()
                ->title('Please create dinomination church district with assigned diocese admin')
                ->body('Since, church districts exist please proceed in creating church')
                ->danger()
                ->send();
                redirect()->to(static::getResource()::getUrl('index'));
            }
            else{
                abort_unless(static::getResource()::canCreate(), 403);
            }
        }else if(auth()->user()->hasRole('Dinomination Admin')){
            Notification::make()
            ->title('Can not create more than one Dinomination Church District')
            ->body('Each Dinomination can have only one Dinomination Church DIstrict.')
            ->danger()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }
        else{
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

    protected function handleRecordCreation(array $data): Model
    {
        $regions = []; $districts = []; $wards = []; $all_districts = []; $all_wards = [];

        foreach($data['church district details'] as $key => $churchdistrict){
            $regions[] = $churchdistrict['regions'];
            $districts[] =[
                    $regions[$key] => $churchdistrict['districts']
                ];
            $wards[] =[
                    $regions[$key] => Ward::whereIn('district_id', $churchdistrict['districts'])->pluck('id'),
                ];
            // dd(Ward::whereIn('district_id', $churchdistrict['districts'])->pluck('id')->toArray());
        }

        if(auth()->user()->hasRole('Diocese Admin')){
            $model = static::getModel()::create([
                'name' => $data['name'],
                'status' => $data['status'],
                'regions' => $regions,
                'districts' => $districts,
                'all_wards' => true,
                'wards' => $wards,
                'diocese_id' => $data['diocese']
            ]);
        }else{
            $model = static::getModel()::create([
                'name' => $data['name'],
                'status' => $data['status'],
                'regions' => $regions,
                'districts' => $districts,
                'all_wards' => true,
                'wards' => $wards,
                'diocese_id' => $data['diocese_id']
            ]);
        }

        return $model;
    }
}
