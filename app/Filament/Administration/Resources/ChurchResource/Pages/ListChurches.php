<?php

namespace App\Filament\Administration\Resources\ChurchResource\Pages;

use App\Filament\Administration\Resources\ChurchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\Church;
use App\Models\ChurchDistrict;

class ListChurches extends ListRecords
{
    protected static string $resource = ChurchResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('view-any Church')){

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
            ->label(fn() => auth()->user()->hasRole('Diocese Admin') ? 'Create Diocese Church' : (auth()->user()->hasRole('ChurchDistrict Admin') ? 'Create Church' : (auth()->user()->hasRole('Parish Admin') ? 'Create SubParish' : 'Create Dinomination Church'))),
        ];
    }
}
