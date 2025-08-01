<?php

namespace App\Filament\Resources\WardResource\Pages;

use App\Filament\Resources\WardResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageWards extends ManageRecords
{
    protected static string $resource = WardResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Ward')){

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
        if(auth()->user()->checkPermissionTo('create Ward')){
            return [];
        }else{
            return [
                Actions\CreateAction::make(),
            ];
        }
    }

}
