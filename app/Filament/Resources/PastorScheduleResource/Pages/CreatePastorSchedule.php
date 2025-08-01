<?php

namespace App\Filament\Resources\PastorScheduleResource\Pages;

use App\Filament\Resources\PastorScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePastorSchedule extends CreateRecord
{
    protected static string $resource = PastorScheduleResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create PastorSchedule')){
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
}
