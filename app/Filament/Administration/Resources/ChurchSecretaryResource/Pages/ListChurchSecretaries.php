<?php

namespace App\Filament\Administration\Resources\ChurchSecretaryResource\Pages;

use App\Filament\Administration\Resources\ChurchSecretaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\Church;
use App\Models\ChurchSecretary;
use Illuminate\Support\Facades\Auth;

class ListChurchSecretaries extends ListRecords
{
    protected static string $resource = ChurchSecretaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label(function () {
                if(Auth::guard('admin')->user()->hasRole('Dinomination Admin')){
                    return 'Create ArchBishop Secretary';
                }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin')){
                    return 'Create Bishop Secretary';
                }else{
                    return 'Create Pastor Secretary';
                }
            })
            ->before(function(Actions\CreateAction $action){
                if(Church::all()->count() <= 0){
                    Notification::make()
                        ->title('Unfortunately there is no church created')
                        ->warning()
                        ->send();

                    $action->halt();
                }
            })
            ->disabled(! auth()->user()->checkPermissionTo('create ChurchSecretary'))
            ->visible(function(){
                if(Auth::guard('admin')->user()->hasRole('Dinomination Admin') && ChurchSecretary::where('title', 'ArchBishop Secretary')->where('status', 'active')->exists()  && auth()->user()->checkPermissionTo('create ChurchSecretary')){
                    return false;
                }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin')&& auth()->user()->checkPermissionTo('create ChurchSecretary')){
                    return true;
                }else {
                    if(auth()->user()->checkPermissionTo('create ChurchSecretary')){
                        return true;
                    }else{
                        return false;
                    }
                }
            }),
        ];
    }
}
