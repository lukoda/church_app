<?php

namespace App\Filament\Resources\PastorResource\Pages;

use App\Filament\Resources\PastorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\Church;

class ListPastors extends ListRecords
{
    protected static string $resource = PastorResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Pastor')){

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
            ->before(function(Actions\CreateAction $action){
                if(Church::all()->count() > 0){
                    Notification::make()
                        ->title('Unfortunately there is no church created')
                        ->warning()
                        ->send();

                    $action->halt();
                }
            })
            ->disabled(! auth()->user()->checkPermissionTo('create Pastor'))
            ->visible(auth()->user()->checkPermissionTo('create Pastor')),
        ];
    }
}
