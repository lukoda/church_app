<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\UserRole;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create User')){
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

    protected function afterCreate(): void
    {
        foreach($this->data['roles']  as $role){
            $assign_userRoles = new UserRole;
            $assign_userRoles->user_id = $this->record->id;
            $assign_userRoles->role_id = $role;
            $assign_userRoles->save();
        }
    }
}
