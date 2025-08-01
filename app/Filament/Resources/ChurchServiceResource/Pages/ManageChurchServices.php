<?php

namespace App\Filament\Resources\ChurchServiceResource\Pages;

use App\Filament\Resources\ChurchServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageChurchServices extends ManageRecords
{
    protected static string $resource = ChurchServiceResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any ChurchService')){

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
