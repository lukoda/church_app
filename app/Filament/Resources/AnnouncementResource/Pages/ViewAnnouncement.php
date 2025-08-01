<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use App\Models\Announcement;

class ViewAnnouncement extends ViewRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view Announcement')){
            if(Announcement::whereId($this->getRecord()->id)->where('dinomination_id', auth()->user()->dinomination_id)->exists()){
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
