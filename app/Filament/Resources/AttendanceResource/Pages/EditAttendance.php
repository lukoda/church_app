<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('update Attendance')){
            abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
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
