<?php

namespace App\Filament\Administration\Resources\ChurchDistrictResource\Pages;

use App\Filament\Administration\Resources\ChurchDistrictResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\Diocese;
use App\Models\Church;

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
            Actions\CreateAction::make()
            ->before(function(Actions\CreateAction $action){
                if(Diocese::all()->count() <= 0){
                    Notification::make()
                        ->title('Unfortunately there is no diocese created')
                        ->warning()
                        ->send();

                    $action->halt();
                }
            })
            ->visible(function() {
                if(Auth::guard('admin')->user()->hasRole('Dinomination Admin') && Church::where('church_type', 'dinomination')->exists()){
                    return false;
                }else{
                    return true;
                }
            })
        ];
    }
}
