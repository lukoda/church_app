<?php

namespace App\Filament\Resources\DioceseResource\Pages;

use App\Filament\Resources\DioceseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListDioceses extends ListRecords
{
    protected static string $resource = DioceseResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Diocese')){

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
            Actions\CreateAction::make(),
        ];
    }
}
