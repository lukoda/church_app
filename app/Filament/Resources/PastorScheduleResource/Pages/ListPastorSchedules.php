<?php

namespace App\Filament\Resources\PastorScheduleResource\Pages;

use App\Filament\Resources\PastorScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListPastorSchedules extends ListRecords
{
    protected static string $resource = PastorScheduleResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any PastorSchedule')){

        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->disabled(! (auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('create PastorSchedule')))
            ->visible(auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('create PastorSchedule')),
        ];
    }
}
