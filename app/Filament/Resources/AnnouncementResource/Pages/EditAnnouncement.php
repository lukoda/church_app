<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\Diocese;
use App\Models\ChurchDistrict;
use App\Models\Church;
use Filament\Notifications\Notification;
use App\Models\Announcement;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('update Announcement')){
            if(Announcement::whereId($this->getRecord()->id)->where('user_id', auth()->user()->id)->exists()){
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
            $data['all_dioceses'] = $data['all_dioceses'] ?? true;
            $data['all_church_districts'] = $data['all_church_districts'] ?? true;
            $data['all_churches'] = $data['all_churches'] ?? true;
            $data['all_sub_parishes'] = $data['all_sub_parishes'] ?? true;
            $data['all_jumuiyas'] = $data['all_jumuiyas'] ?? true;
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
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

        $record->update([
            'begin_date' => $data['begin_date'],
            'duration' => $data['duration'],
            'end_date' => $data['end_date'],
            'level' => $data['level'],
            'all_dioceses' => $data['all_dioceses'] ?? Null,
            'diocese' =>  $data['level'] == 'jimbo' ? $diocese : (array_key_exists('all_dioceses', $data) ? ($data['all_dioceses'] == true ? Diocese::where('dinomination_id', auth()->user()->church->churchDistrict->diocese->dinomination_id)->pluck('id')->toArray() : $data['diocese']) : Null),
            'all_church_districts' => $data['all_church_districts'] ?? Null,
            'church_districts' => $data['level'] == 'church' ? $church_district : $data['church_districts'] ?? Null,
            'all_churches' => $data['all_churches'] ?? Null,
            'church' => $data['level'] == 'sub_parish' ? $parish : ($data['level'] == 'jumuiya' || $data['level'] == 'church_members' ? Church::whereId(auth()->user()->church_id)->pluck('id')->toArray() : $data['church'] ?? Null),
            'all_sub_parishes' => $data['all_sub_parishes'] ?? Null,
            'sub_parish' => $data['sub_parish'] ?? Null,
            'all_jumuiyas' => $data['all_jumuiyas'] ?? Null,
            'jumuiya' => $data['jumuiya'] ?? Null,
            'status' => $data['status'] ?? Null,
            'message' => $data['message'] ?? Null,
            'documents' => $data['documents'] ?? Null,
            'published_level' => $data['level'] == 'diocese' ? 'diocese' : ($data['level'] == 'jimbo' ? 'jimbo' : ($data['level'] == 'church' ? 'singular' : Null))
        ]);
    
        return $record;
    }
}
