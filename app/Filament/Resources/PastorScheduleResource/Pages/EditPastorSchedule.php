<?php

namespace App\Filament\Resources\PastorScheduleResource\Pages;

use App\Filament\Resources\PastorScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPastorSchedule extends EditRecord
{
    protected static string $resource = PastorScheduleResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('update PastorSchedule')){
            abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }

    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->disabled(! (auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('delete PastorSchedule')))
            ->visible(auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('delete PastorSchedule')),
        ];
    }
}
