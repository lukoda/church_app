<?php

namespace App\Filament\Administration\Resources\ChurchDistrictResource\Pages;

use App\Filament\Administration\Resources\ChurchDistrictResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ListChurchDistricts extends ListRecords
{
    protected static string $resource = ChurchDistrictResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('view-any ChurchDistrict')){

        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to('/administration');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
