<?php

namespace App\Filament\Resources\LogOfferingResource\Pages;

use App\Filament\Resources\LogOfferingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListLogOfferings extends ListRecords
{
    protected static string $resource = LogOfferingResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any LogOffering')){

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
                ->visible(auth()->user()->checkPermissionTo('create LogOffering')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LogOfferingResource\Widgets\LogOfferingOverview::class,
        ];
    }
}
