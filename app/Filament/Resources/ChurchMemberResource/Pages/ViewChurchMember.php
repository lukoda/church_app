<?php

namespace App\Filament\Resources\ChurchMemberResource\Pages;

use App\Filament\Resources\ChurchMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use App\Models\ChurchMember;

class ViewChurchMember extends ViewRecord
{
    protected static string $resource = ChurchMemberResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view ChurchMember')){
            if(ChurchMember::whereId($this->getRecord()->id)->where('church_id', auth()->user()->church_id)->exists()){
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
}
