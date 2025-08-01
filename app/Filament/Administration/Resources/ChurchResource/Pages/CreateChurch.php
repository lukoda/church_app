<?php

namespace App\Filament\Administration\Resources\ChurchResource\Pages;

use App\Filament\Administration\Resources\ChurchResource;
use Filament\Actions;
use App\Models\Church;
use App\Models\ChurchDistrict;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stevebauman\Location\Facades\Location;
use Storage;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateChurch extends CreateRecord
{
    protected static string $resource = ChurchResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('create Church')){
            if(auth()->user()->hasRole('Diocese Admin') && Church::whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->exists()){
                Notification::make()
                ->title('Can not create more than one Diocese Church')
                ->body('Each Diocese can have only one Diocese Church.')
                ->danger()
                ->send();
                redirect()->to(static::getResource()::getUrl('index'));
            }else{
                abort_unless(static::getResource()::canCreate(), 403);
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
            'region_id' => $data['region'] == Null ? $data['region_id'] : $data['region'],
            'district_id' => $data['district'] == Null ? $data['district_id'] : $data['district'],
            'ward_id' => $data['ward_id'],
            'church_location_status' => $data['church_location_status'],
            'pictures' => $data['pictures'],
            'church_district_id' => auth()->user()->hasRole('Diocese Admin') ? $data['church_district'] : (auth()->user()->hasRole('Parish Admin') ? Church::where('Id', auth()->user()->church_id)->pluck('church_district_id')[0] : $data['church_district_id']),
        ]);
    }
}