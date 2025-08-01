<?php

namespace App\Filament\Resources\RoleResource\Pages;;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Super Admin')){
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
