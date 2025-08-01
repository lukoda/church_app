<?php

namespace App\Filament\Resources\ChurchMassResource\Pages;

use App\Filament\Resources\ChurchMassResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use App\Models\ChurchMass;

class ViewChurchMass extends ViewRecord
{
    protected static string $resource = ChurchMassResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view ChurchMass')){
            if(ChurchMass::whereId($this->getRecord()->id)->where('church_id', auth()->user()->church_id)->exists()){
                abort_unless(static::getResource()::canView($this->getRecord()), 403);
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
            Actions\EditAction::make(),
        ];
    }
}
