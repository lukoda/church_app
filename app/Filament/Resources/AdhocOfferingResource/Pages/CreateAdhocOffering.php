<?php

namespace App\Filament\Resources\AdhocOfferingResource\Pages;

use App\Filament\Resources\AdhocOfferingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAdhocOffering extends CreateRecord
{
    protected static string $resource = AdhocOfferingResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('create AdhocOffering')){
            abort_unless(static::getResource()::canCreate(), 403);
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }
    }
}
