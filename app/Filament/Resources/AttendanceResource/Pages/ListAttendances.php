<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Attendance')){

        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(function(){
                    if(Attendance::count() > 0){
                        $last_entry = Attendance::latest()->first();
                        $last_entry_date = Carbon::parse($last_entry->date);
                        if(now() < $last_entry_date->addDay(7)->toDateTimeString()){
                            return true;
                        }else{
                            return false;
                        }
                    }else{
                        return false;
                    }

                }),
        ];
    }
}
