<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewPermission extends ViewRecord
{
    protected static string $resource = PermissionResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Super Admin')){
            abort_unless(static::getResource()::canView($this->getRecord()), 403);
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }
    }

    public function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
