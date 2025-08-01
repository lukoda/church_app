<?php

namespace App\Filament\Resources\AdhocOfferingResource\Pages;

use App\Filament\Resources\AdhocOfferingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\AdhocOffering;

class EditAdhocOffering extends EditRecord
{
    protected static string $resource = AdhocOfferingResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('update AdhocOffering')){
            if(AdhocOffering::whereId($this->getRecord()->id)->where('church_id', auth()->user()->church_id)->exists()){
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
