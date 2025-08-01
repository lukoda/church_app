<?php

namespace App\Filament\Resources\PastorResource\Pages;

use App\Filament\Resources\PastorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChurchMember;
use App\Models\User;
use App\Models\Pastor;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Church;

class CreatePastor extends CreateRecord
{
    protected static string $resource = PastorResource::class;

    protected function authorizeAccess(): void
    {
        if(Church::all()->count() <= 0){
            Notification::make()
            ->title('No church has been registered yet')
            ->warning()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }else{
            if(auth()->user()->checkPermissionTo('create Pastor')){
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
    }

    protected function handleRecordCreation(array $data): Model 
    {
        if(User::where('phone', $this->data['phone'])->exists()){
            $user = User::where('phone', $this->data['phone'])->first();
            $church_member = ChurchMember::where('user_id', $user->id)->first();
            $pastor = Pastor::where('church_member_id', $church_member->id)->first();
            return $pastor;
        }else{
            $user = new User;
            $user->phone = $data['phone'];
            $user->password = Hash::make($data['phone']);
            $user->church_id = $data['church_assigned_id'];
            $user->dinomination_id = auth()->user()->dinomination_id;
            $user->save();

            $church_member = new ChurchMember;
            $church_member->first_name = $data['first_name'];
            $church_member->middle_name = $data['middle_name'];
            $church_member->surname = $data['surname'];
            $church_member->email = $data['email'] ?? Null;
            $church_member->phone = $data['phone'];
            $church_member->gender = $data['gender'];
            $church_member->date_registered = now();
            $church_member->user_id = $user->id;
            $church_member->church_id = $data['church_assigned_id'];
            $church_member->status = 'active';
            $church_member->save();

            $user->assignRole($data['title']);
            
            return static::getModel()::create([
                'church_member_id' => $church_member->id,
                'date_registered' => now(),
                'status' => 'active',
                'title' => $data['title'],
                'church_assigned_id' => $data['church_assigned_id']
            ]);
        }

    }

    protected function getCreatedNotification(): ?Notification
    {
        if(User::where('phone', $this->data['phone'])->exists()){
            $user = User::where('phone', $this->data['phone'])->first();
            $church_member = ChurchMember::where('user_id', $user->id)->first();
            $pastor = Pastor::where('church_member_id', $church_member->id)->first();
            return Notification::make()
                    ->title($pastor->title." is already assigned.")
                    ->body("This Pastor has already been created as ".$pastor->title." in church ".$church_member->church->name)
                    ->danger()
                    ->send();
        }else{
            return Notification::make()
            ->success()
            ->title('Pastor registered')
            ->body('The pastor has been created successfully.');
        }

    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
