<?php

namespace App\Filament\Resources\DinominationResource\Pages;

use App\Filament\Resources\DinominationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageDinominations extends ManageRecords
{
    protected static string $resource = DinominationResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Dinomination')){

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
        if(auth()->user()->checkPermissionTo('create Dinomination')){
            return [];
        }else{
            return [
                Actions\CreateAction::make(),
            ];
        }
    }
}
