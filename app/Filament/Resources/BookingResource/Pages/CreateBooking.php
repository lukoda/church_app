<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Schedule;
use Filament\Notifications\Notification;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create Booking')){
            abort_unless(static::getResource()::canCreate(), 403);
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }
    }

    protected function afterCreate():void
    {
        $schedule = Schedule::whereId($this->record->booked_schedule)->first();
        $schedule->pending_approvals += 1;
        $schedule->save();
    }
}
