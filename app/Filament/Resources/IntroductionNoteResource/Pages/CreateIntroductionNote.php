<?php

namespace App\Filament\Resources\IntroductionNoteResource\Pages;

use App\Filament\Resources\IntroductionNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class CreateIntroductionNote extends CreateRecord
{
    protected static string $resource = IntroductionNoteResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('create IntroductionNote')){
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

    protected function handleRecordCreation(array $data): Model
    {
        $date_of_return = Carbon::parse($data['date_requested'])->startOfWeek()->subDay()->addWeeks($data['sundays_on_leave']);

        $model = static::getModel()::create([
            'from_church_id' => $data['from_church_id'],
            'to_church_id' => $data['to_church_id'],
            'title' => $data['title'],
            'date_requested' => $data['date_requested'],
            'sundays_on_leave' => $data['sundays_on_leave'],
            'description' => $data['description'] ?? Null,
            'region_id' => $data['region_id'] ?? Null,
            'district_id' => $data['district_id'] ?? Null,
            'ward_id' => $data['ward_id'] ?? Null,
            'church_member_id' => $data['church_member_id'],
            'status' => $data['status'],
            'approval_status' => 'Awaiting Approval',
            'date_of_return' => $date_of_return
        ]);

        return $model;
    }
}
