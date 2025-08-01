<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateCard extends CreateRecord
{
    protected static string $resource = CardResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('create Card')){
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
