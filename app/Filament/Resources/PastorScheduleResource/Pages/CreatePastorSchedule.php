<?php

namespace App\Filament\Resources\PastorScheduleResource\Pages;

use App\Filament\Resources\PastorScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Models\Pastor;

class CreatePastorSchedule extends CreateRecord
{
    protected static string $resource = PastorScheduleResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create PastorSchedule')){
            if(Pastor::where('church_assigned_id', auth()->user()->church_id)->where('status', 'active')->exists()){
                abort_unless(static::getResource()::canCreate(), 403);
            }else{
                Notification::make()
                ->title('No Registered Pastors')
                ->body('Please contact your administrator, to assign pastor to a church to add his/her schedule.')
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
