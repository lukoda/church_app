<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Super Admin')){
            abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
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
