<?php

namespace App\Filament\Administration\Resources\PastorResource\Pages;

use App\Filament\Administration\Resources\PastorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\Church;
use App\Models\Pastor;
use Illuminate\Support\Facades\Auth;

class ListPastors extends ListRecords
{
    protected static string $resource = PastorResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('view-any Pastor')){

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
            ->label(function () {
                if(Auth::guard('admin')->user()->hasRole('Dinomination Admin')){
                    return 'Create ArchBishop';
                }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin')){
                    return 'Create Bishop';
                }else{
                    return 'Create Pastor';
                }
            })
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
            ->visible(function(){
                if(Auth::guard('admin')->user()->hasRole('Dinomination Admin') && Pastor::where('title', 'ArchBishop')->where('status', 'active')->exists() && auth()->user()->checkPermissionTo('create Pastor')){
                    return false;
                }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin') && auth()->user()->checkPermissionTo('create Pastor')){
                    return false;
                }else {
                    if(auth()->user()->checkPermissionTo('create Pastor')){
                        return true;
                    }else{
                        return false;
                    }
                }
            }),
        ];
    }
}
