<?php

namespace App\Filament\Resources\DistrictResource\Pages;

use App\Filament\Resources\DistrictResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageDistricts extends ManageRecords
{
    protected static string $resource = DistrictResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any District')){

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
        if(auth()->user()->checkPermissionTo('create District')){
            return [];
        }else{
            return [
                Actions\CreateAction::make(),
            ];
        }
    }
}
