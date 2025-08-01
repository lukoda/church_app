<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\Church;
use App\Models\Diocese;
use App\Models\ChurchDistrict;
use Filament\Notifications\Notification;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create Announcement')){
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {   
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $diocese; $church_district; $parish; $members_church;
        $church = Church::whereId(auth()->user()->church_id)->first();
        if($data['level'] == 'jimbo'){
            $diocese = Diocese::whereId($church->churchDistrict->diocese_id)->pluck('id');
        }else if($data['level'] == 'church'){
            $church_district = ChurchDistrict::whereId($church->churchDistrict->id)->pluck('id');
        }else if($data['level'] == 'sub_parish'){
            $parish = Church::whereId(auth()->user()->church_id)->pluck('parent_church');
        }

        return static::getModel()::create([
            'user_id' => $data['user_id'],
            'dinomination_id' => $data['dinomination_id'],
            'message' => $data['message'],
            'status' => $data['status'],
            'level' => $data['level'],
            'all_dioceses' => $data['all_dioceses'] ?? Null,
            'diocese' => $data['level'] == 'jimbo' ? $diocese : (array_key_exists('all_dioceses', $data) ? ($data['all_dioceses'] == true ? Diocese::where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->pluck('id')->toArray() : $data['diocese']) : Null),
            'all_church_districts' => $data['all_church_districts'] ?? Null,
            'church_districts' => $data['level'] == 'church' ? $church_district : $data['church_districts'] ?? Null,
            'all_churches' => $data['all_churches'] ?? Null,
            'church' => $data['level'] == 'sub_parish' ? $parish : ($data['level'] == 'jumuiya' || $data['level'] == 'church_members' ? Church::whereId(auth()->user()->church_id)->pluck('id')->toArray() : $data['church'] ?? Null),
            'all_sub_parishes' => $data['all_sub_parishes'] ?? Null,
            'sub_parish' => $data['sub_parish'] ?? Null,
            'all_jumuiyas' => $data['all_jumuiyas'] ?? Null,
            'jumuiya' => $data['jumuiya'] ?? Null,
            'documents' => $data['documents'] ?? Null,
            'begin_date' => $data['begin_date'],
            'duration' => $data['duration'],
            'end_date' => $data['end_date'],
            'published_level' => ($data['level'] == 'church_members' ? 'all_church_members' : ($data['level'] == 'sub_parish' ? 'sub_parish' : ($data['level'] == 'jumuiya' ? 'jumuiya' : ($data['level'] == 'diocese' ? 'diocese' : ($data['level'] == 'jimbo' ? 'jimbo' : ($data['level'] == 'church' ? 'singular' : Null))))))
        ]);
    }
}
