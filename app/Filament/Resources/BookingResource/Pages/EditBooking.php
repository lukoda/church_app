<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Schedule;
use Filament\Notifications\Notification;
use App\Models\Booking;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('update Booking')){
            if(Booking::whereId($this->getRecord()->id)->where('church_id', auth()->user()->church_id)->exists()){
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
            Actions\DeleteAction::make()
                ->after(function(){
                    $schedule = Schedule::whereId($this->record->booked_schedule)->first();
                    $schedule->pending_approvals += 1;
                    $schedule->save();
                }),
        ];
    }
}
