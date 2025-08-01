<?php

namespace App\Filament\Resources\JumuiyaResource\Pages;

use App\Filament\Resources\JumuiyaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageJumuiyas extends ManageRecords
{
    protected static string $resource = JumuiyaResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Jumuiya')){

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
        if(auth()->user()->checkPermissionTo('create Jumuiya')){
            return [
                Actions\CreateAction::make(),
            ];
        }else{
            return [];
        }
    }
}
