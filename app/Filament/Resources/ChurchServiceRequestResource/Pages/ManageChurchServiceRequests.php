<?php

namespace App\Filament\Resources\ChurchServiceRequestResource\Pages;

use App\Filament\Resources\ChurchServiceRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageChurchServiceRequests extends ManageRecords
{
    protected static string $resource = ChurchServiceRequestResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any ChurchServiceRequest')){

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
        if(auth()->user()->checkPermissionTo('create ChurchServiceRequest')){
            return [];
        }else{
            return [
                Actions\CreateAction::make(),
            ];
        }
    }
}
