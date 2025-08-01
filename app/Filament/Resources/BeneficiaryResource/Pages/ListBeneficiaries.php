<?php

namespace App\Filament\Resources\BeneficiaryResource\Pages;

use App\Filament\Resources\BeneficiaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListBeneficiaries extends ListRecords
{
    protected static string $resource = BeneficiaryResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Beneficiary')){

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
            Actions\CreateAction::make()
            ->visible(auth()->user()->hasRole('Church Secretary') || auth()->user()->checkPermissionTo('create Beneficiary')),
        ];
    }
}
