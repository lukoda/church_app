<?php

namespace App\Filament\Resources\ChurchResource\Pages;

use App\Filament\Resources\ChurchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\models\Church;
use Filament\Notifications\Notification;

class EditChurch extends EditRecord
{
    protected static string $resource = ChurchResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('update Church')){
            if(auth()->user()->hasRole('Parish Admin') && Church::whereId($this->getrecord()->id)->where('parent_church', auth()->user()->church_id)->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(auth()->user()->hasRole('ChurchDistrict Admin') && Church::whereId($this->getRecord()->id)->where('church_district_id', auth()->user()->church->churchDistrict->id)->exists()){
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
                    }
                    $action->cancel();
                }),
        ];
    }
}
