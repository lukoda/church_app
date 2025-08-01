<?php

namespace App\Filament\Resources\CommitteeResource\Pages;

use App\Filament\Resources\CommitteeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageCommittees extends ManageRecords
{
    protected static string $resource = CommitteeResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Committee')){

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
        if(auth()->user()->checkPermissionTo('create Committee')){
            return [];
        }else{
            return [
                Actions\CreateAction::make(),
            ];
        }
    }
}
