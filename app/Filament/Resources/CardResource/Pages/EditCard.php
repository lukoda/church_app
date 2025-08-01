<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\CardPledge;
use Filament\Notifications\Notification;
use App\Models\Card;

class EditCard extends EditRecord
{
    protected static string $resource = CardResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('update Card')){
            if(Card::whereId($this->getRecord()->id)->where('church_id', auth()->user()->church_id)->exists()){
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
