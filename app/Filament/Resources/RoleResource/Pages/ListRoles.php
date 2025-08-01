<?php

namespace App\Filament\Resources\RoleResource\Pages;;

use App\Filament\Resources\RoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Super Admin')){

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
            CreateAction::make(),
        ];
    }
}
