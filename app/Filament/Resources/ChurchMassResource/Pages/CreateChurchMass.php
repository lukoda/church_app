<?php

namespace App\Filament\Resources\ChurchMassResource\Pages;

use App\Filament\Resources\ChurchMassResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateChurchMass extends CreateRecord
{
    protected static string $resource = ChurchMassResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create ChurchMass')){
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
