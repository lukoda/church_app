<?php

namespace App\Filament\Resources\ChurchResource\Pages;

use App\Filament\Resources\ChurchResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stevebauman\Location\Facades\Location;
use Storage;
use Filament\Notifications\Notification;

class CreateChurch extends CreateRecord
{
    protected static string $resource = ChurchResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create Church')){
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
        // $geoLocation = static::getModel()::create($data);
        // $geoLocationPoint = Location::get($_SERVER['REMOTE_ADDR']);
        // $geoLocation->church_location = DB::raw("GeoFromText('POINT({$geoLocationPoint->latitude},{$geoLocationPoint->longitude}')");
        // $geoLocation->save();

        return static::getModel()::create([
            'name' => $data['name'],
            'church_type' => $data['church_type'],
            'parent_church' => array_key_exists('parent_church', $data) ? $data['parent_church'] : Null,
            'region_id' => $data['region'] == Null ? $data['region_id'] : $data['region'][0],
            'district_id' => $data['district'] == Null ? $data['district_id'] : $data['district'][0],
            'ward_id' => $data['ward_id'],
            'church_location_status' => $data['church_location_status'],
            'pictures' => $data['pictures'],
            'church_district_id' => $data['church_district_id'],
        ]);
    }
}
