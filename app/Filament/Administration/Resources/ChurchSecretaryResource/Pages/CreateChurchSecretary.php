<?php

namespace App\Filament\Administration\Resources\ChurchSecretaryResource\Pages;

use App\Filament\Administration\Resources\ChurchSecretaryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Church;
use App\Models\User;
use App\Models\ChurchDistrict;
use App\Models\ChurchMember;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\ChurchSecretary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateChurchSecretary extends CreateRecord
{
    protected static string $resource = ChurchSecretaryResource::class;

    protected function authorizeAccess(): void
    {
        if(Church::all()->count() <= 0){
            Notification::make()
            ->title('No church has been registered yet')
            ->warning()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }else{
            if(Auth::guard('admin')->user()->checkPermissionTo('create ChurchSecretary')){
                if(Auth::guard('admin')->user()->hasRole('Parish Admin')){

                    if(ChurchSecretary::where('title', 'Church Secretary')->where('church_assigned_id', auth()->user()->church_id)
                       ->where('status', 'active')->exists()){
                         Notification::make()
                            ->title('There can not be more than one Church Secretary')
                            ->body('A Church can have only one church secretary.')
                            ->warning()
                            ->send();

                        redirect()->to(static::getResource()::getUrl('index'));

                    }else{
                        abort_unless(static::getResource()::canCreate(), 403);
                    }


                //     if(ChurchSecretary::where('title', 'ArchBishop Secretary')->where('status', 'active')->exists()){
                //         Notification::make()
                //         ->title('There can not be more than one ArchBishop Secretary')
                //         ->body('A Dinomination can have only one ArchBishop Secretary.')
                //         ->warning()
                //         ->send();
                //         redirect()->to(static::getResource()::getUrl('index'));
                //     }else{
                //         abort_unless(static::getResource()::canCreate(), 403);
                //     }
                // }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin')){
                //     if(ChurchScretary::where('title', 'Bishop Secretary')->where('status', 'active')->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->pluck('id'))->exists()){
                //         Notification::make()
                //         ->title('There can not be more than one Bishop Secretary')
                //         ->body('A Diocese can have only one Bishop Secretary.')
                //         ->warning()
                //         ->send();
                //         redirect()->to(static::getResource()::getUrl('index'));
                //     }else{
                //         abort_unless(static::getResource()::canCreate(), 403);
                //     }
                // }else{
                //     abort_unless(static::getResource()::canCreate(), 403);
                // }
            }else if(Auth::guard('admin')->user()->hasRole('Dinomination Admin')){

                    if(ChurchSecretary::where('title', 'ArchBishop Secretary')->where('status', 'active')->exists()){
                        Notification::make()
                        ->title('There can not be more than one ArchBishop Secretary')
                        ->body('A Dinomination can have only one ArchBishop Secretary.')
                        ->warning()
                        ->send();
                        redirect()->to(static::getResource()::getUrl('index'));
                    }else{
                        abort_unless(static::getResource()::canCreate(), 403);
                    }

            }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin')){

                    if(ChurchSecretary::where('title', 'Bishop Secretary')->where('status', 'active')->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->pluck('id'))->exists()){
                        Notification::make()
                        ->title('There can not be more than one Bishop Secretary')
                        ->body('A Diocese can have only one Bishop Secretary.')
                        ->warning()
                        ->send();
                        redirect()->to(static::getResource()::getUrl('index'));
                    }else{
                        abort_unless(static::getResource()::canCreate(), 403);
                    }

            }else if(Auth::guard('admin')->user()->hasRole('ChurchDistrict Admin'))
            {
                    if(ChurchSecretary::whereIn('church_assigned_id', Church::where('church_district_id', auth()->user()->church_district_id)->pluck('id'))->where('title', 'ChurchDistrict Secretary')->orwhere('title', 'Church Secretary')->where('status', 'active')->exists()){
                        Notification::make()
                        ->title('There can not be more than one Church Secretary')
                        ->body('A Church District can have only one Church Secretary.')
                        ->warning()
                        ->send();
                        redirect()->to(static::getResource()::getUrl('index'));
                    }else{
                        abort_unless(static::getResource()::canCreate(), 403);
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
}

    protected function handleRecordCreation(array $data): Model 
    {
        $null_church_secretary = new ChurchSecretary;
        if(ChurchSecretary::whereIn('church_assigned_id', Church::where('church_district_id', auth()->user()->church_district_id)->pluck('id'))->where('title', $this->data['title'])->where('status', 'active')->exists() || ChurchSecretary::whereIn('church_assigned_id', Church::where('parent_church', auth()->user()->church_id)->pluck('id'))->where('title', $this->data['title'])->where('status', 'active')->exists()){
            redirect()->to($this->getResource()::getUrl('index'));
            Notification::make()
            ->title('Church Already Assigned '.$this->data['title'])
            ->body('This church is already assigned a '.$this->data['title'])
            ->warning()
            ->send();

            return $null_church_secretary;
        }else if(User::where('phone', $this->data['phone'])->exists()){
                $user = User::where('phone', $this->data['phone'])->first();
                $church_member = ChurchMember::where('user_id', $user->id)->first();
                if($church_member == null){
                    redirect()->to('administration/church-secretaries');
                    Notification::make()
                    ->success()
                    ->title('User Already Registered')
                    ->body('This phone number has already been registered.');

                    return $null_pastor;
                }else{
                    $churchSecretary = ChurchSecretary::where('church_member_id', $church_member->id)->first();
                    return $churchSecretary;
                }
        }else{

            DB::beginTransaction();

            try{
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
    
                $user->assignRole('Church Secretary');
                
                DB::commit();

                return static::getModel()::create([
                    'church_member_id' => $church_member->id,
                    'date_registered' => now(),
                    'status' => 'active',
                    'title' => $data['title'],
                    'church_assigned_id' => $data['church_assigned_id']
                ]);
            }
            catch(Throwable $e){
                DB::rollBack();

                redirect()->to('administration/church-secretaries');

                Notification::make()
                ->success()
                ->title('Failed To Create Bishop Secretary')
                ->body('Please try again, or contact the administrator for support');

                return $null_church_secretary;
            }

        }

    }

    protected function getCreatedNotification(): ?Notification
    {
        $church_district_churches = Church::where('church_district_id', auth()->user()->church_district_id)->pluck('id');
        if(ChurchSecretary::whereIn('church_assigned_id', $church_district_churches)->where('title', 'Senior Secretary')->where('status', 'active')->exists() || ChurchSecretary::whereIn('church_assigned_id', $church_district_churches)->where('title', 'Church Secretary')->where('status', 'active')->exists() || ChurchSecretary::whereIn('church_assigned_id', Church::where('parent_church', auth()->user()->church_id)->pluck('id'))->where('title', 'SubParish Secretary')->where('status', 'active')->exists()){
            return null;
        }else{
            if(User::where('phone', $this->data['phone'])->exists()){
                $user = User::where('phone', $this->data['phone'])->first();
                $church_member = ChurchMember::where('user_id', $user->id)->first();
                $churchSecretary = ChurchSecretary::where('church_member_id', $church_member->id)->first();
                return Notification::make()
                        ->title($churchSecretary->title." is already assigned.")
                        ->body("This Pastor has already been created as ".$churchSecretary->title." in church ".$churchSecretary->church->name)
                        ->success()
                        ->send();
            }else{
                return Notification::make()
                ->success()
                ->title('The Church Secretary has been registered')
                ->body('The Church Secreatry has been created successfully.');
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
