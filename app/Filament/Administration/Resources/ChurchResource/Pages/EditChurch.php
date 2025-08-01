<?php

namespace App\Filament\Administration\Resources\ChurchResource\Pages;

use App\Filament\Administration\Resources\ChurchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Church;
use App\Models\ChurchDistrict;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditChurch extends EditRecord
{
    protected static string $resource = ChurchResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('update Church')){
            if(Auth::guard('admin')->user()->hasRole('Parish Admin') && Church::whereId($this->getrecord()->id)->where('parent_church', auth()->user()->church_id)->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(Auth::guard('admin')->user()->hasRole('ChurchDistrict Admin') && Church::whereId($this->getRecord()->id)->where('church_district_id', auth()->user()->church_district_id)->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin') && Church::whereId($this->getRecord()->id)->whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else{
                Notification::make()
                ->title('Page Not Found')
                ->body('Sorry, the requested page does not exist.')
                ->danger()
                ->send();
            }
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }

    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->action(function(Actions\DeleteAction $action, Church $record){
                    if($record->church_members->count() > 0){
                        Notification::make()
                            ->warning()
                            ->title('Church has members')
                            ->body('Can\'t delete church has members')
                            ->persistent()
                            ->send();

                        $action->cancel();
                    }else{
                        $this->record->delete();

                        Notification::make()
                        ->warning()
                        ->title('Success')
                        ->body('Church Deleted Successfully')
                        ->persistent()
                        ->send();
                    }

                }),
        ];
    }
}
