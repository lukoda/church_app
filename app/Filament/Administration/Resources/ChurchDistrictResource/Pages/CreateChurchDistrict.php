<?php

namespace App\Filament\Administration\Resources\ChurchDistrictResource\Pages;

use App\Filament\Administration\Resources\ChurchDistrictResource;
use App\Models\Ward;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateChurchDistrict extends CreateRecord
{
    protected static string $resource = ChurchDistrictResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('create ChurchDistrict')){
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

        $model = static::getModel()::create([
            'name' => $data['name'],
            'status' => $data['status'],
            'regions' => $regions,
            'districts' => $districts,
            'all_wards' => true,
            'wards' => $wards,
            'diocese_id' => $data['diocese_id']
        ]);

        return $model;
    }
}
