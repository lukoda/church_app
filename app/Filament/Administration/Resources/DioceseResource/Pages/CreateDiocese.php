<?php

namespace App\Filament\Administration\Resources\DioceseResource\Pages;

use App\Filament\Administration\Resources\DioceseResource;
use App\Models\District;
use App\Models\Region;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateDiocese extends CreateRecord
{
    protected static string $resource = DioceseResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('create Diocese')){
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
        // dd($data);
        $regions = []; $districts = []; $all_districts = [];
        foreach($data['diocese details'] as $key => $diocese){
            $regions[] = $diocese['regions'];
            $all_districts[] = $diocese['all_districts'];
            if($diocese['all_districts'] == true){
                $districts[] = [
                    $regions[$key] => District::whereRegionId(Region::whereName($regions[$key])->pluck('id'))->pluck('id'),
                ];
            }else{
                $districts[] =[
                    $regions[$key] => $diocese['districts']
                ];
            }
        }

            $model = static::getModel()::create([
                'name' => $data['name'],
                'status' => $data['status'],
                'regions' => $regions,
                'districts' => $districts,
                'all_districts' => $all_districts,
                'dinomination_id' => $data['dinomination_id']
            ]);

        return $model;
    }
}
