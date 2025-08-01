<?php

namespace App\Filament\Resources\RoleResource\Pages;;

use App\Filament\Resources\RoleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->chasRole('Super Admin')){
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
