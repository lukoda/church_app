<?php

namespace App\Filament\Administration\Resources\AdminResource\Pages;

use App\Filament\Administration\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\Diocese;
use App\Models\ChurchDistrict;
use App\Models\Church;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->hasRole('Dinomination Admin')){
            $diocese_admin = Admin::whereNotNull('diocese_id')->pluck('diocese_id');
            if(Diocese::whereNotIn('id', $diocese_admin)->count() > 0){
                abort_unless(static::getResource()::canCreate(), 403);
            }else{
                Notification::make()
                ->title('All registered Dioceses Have already been assigned an Admin.')
                ->body('Can not add more than one admin to one diocese')
                ->warning()
                ->send();
                redirect()->to(static::getResource()::getUrl('index'));
            }
        }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin')){
            $district_admin = Admin::whereNotNull('church_district_id')->pluck('church_district_id');
            if(ChurchDistrict::whereNotIn('id', $district_admin)->count() > 0){
                abort_unless(static::getResource()::canCreate(), 403);
            }else{
                Notification::make()
                ->title('All registered Church Districts Have already been assigned an Admin.')
                ->body('Can not add more than one admin to one Church District')
                ->warning()
                ->send();
                redirect()->to(static::getResource()::getUrl('index')); 
            }
        }else if(Auth::guard('admin')->user()->hasRole('ChurchDistrict Admin')){
            $church_admin = Admin::whereNotNull('church_id')->pluck('church_id');
            if(Church::whereNotIn('id', $church_admin)->count() > 0){
                abort_unless(static::getResource()::canCreate(), 403); 
            }else{
                Notification::make()
                ->title('All registered Churches Have already been assigned an Admin.')
                ->body('Can not add more than one admin to one Church')
                ->warning()
                ->send();
                redirect()->to(static::getResource()::getUrl('index')); 
            }
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create([
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'church_level' => $data['church_level'],
            'diocese_id' => $data['diocese_id'] ?? Null,
            'church_district_id' => $data['church_district_id'] ?? Null,
            'church_id' => $data['church_id'] ?? Null,
            'dinomination_id' => $data['dinomination_id']
        ]);
    }

    protected function afterCreate(): void
    {
        $admin = Admin::whereId($this->getRecord()->id)->first();
        if($this->getRecord()->church_level == 'Diocese'){
            $admin->assignRole('Diocese Admin');
        }else if($this->getRecord()->church_level == 'ChurchDistrict'){
            $admin->assignRole('ChurchDistrict Admin');
        }else if($this->getRecord()->church_level == 'Parish'){
            $admin->assignRole('Parish Admin');
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
