<?php

namespace App\Filament\Resources\RegionResource\Pages;

use App\Filament\Resources\RegionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageRegions extends ManageRecords
{
    protected static string $resource = RegionResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Region')){

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
        if(auth()->user()->checkPermissionTo('create Region')){
            return [];
        }else{
            return [
                Actions\CreateAction::make(),
            ];
        }
    }
}
