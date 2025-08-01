<?php

namespace App\Filament\Resources\ChurchMassResource\Pages;

use App\Filament\Resources\ChurchMassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListChurchMasses extends ListRecords
{
    protected static string $resource = ChurchMassResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any ChurchMass')){

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
            Actions\CreateAction::make(),
        ];
    }
}
