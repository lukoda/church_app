<?php

namespace App\Filament\Administration\Resources\PastorResource\Pages;

use App\Filament\Administration\Resources\PastorResource;
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
use App\Models\ChurchDistrict;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            if(Auth::guard('admin')->user()->checkPermissionTo('create Pastor')){
                if(Auth::guard('admin')->user()->hasRole('Dinomination Admin')){
                    if(Pastor::where('title', 'ArchBishop')->where('status', 'active')->exists()){
                        Notification::make()
                        ->title('There can not be more than one ArchBishop')
                        ->body('A Dinomination can have only one ArchBishop.')
                        ->warning()
                        ->send();
                        redirect()->to(static::getResource()::getUrl('index'));
                    }else{
                        abort_unless(static::getResource()::canCreate(), 403);
                    }
                }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin')){
                    if(Pastor::where('title', 'Bishop')->where('status', 'active')->whereIn('church_assigned_id', Church::whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->pluck('id'))->exists()){
                        Notification::make()
                        ->title('There can not be more than one Bishop')
                        ->body('A Diocese can have only one Bishop.')
                        ->warning()
                        ->send();
                        redirect()->to(static::getResource()::getUrl('index'));
                    }else{
                        abort_unless(static::getResource()::canCreate(), 403);
                    }
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

    protected function handleRecordCreation(array $data): Model 
    {
        $null_pastor = new Pastor;
        if(Pastor::whereIn('church_assigned_id', Church::where('church_district_id', auth()->user()->church_district_id)->pluck('id'))->where('title', $this->data['title'])->where('status', 'active')->exists() || Pastor::whereIn('church_assigned_id', Church::where('parent_church', auth()->user()->church_id)->pluck('id'))->where('title', $this->data['title'])->where('status', 'active')->exists()){
            redirect()->to($this->getResource()::getUrl('index'));
            Notification::make()
            ->title('Church Already Assigned '.$this->data['title'])
            ->body('This church is already assigned a '.$this->data['title'])
            ->warning()
            ->send();

            return $null_pastor;
        }else if(User::where('phone', $this->data['phone'])->exists()){
                $user = User::where('phone', $this->data['phone'])->first();
                $church_member = ChurchMember::where('user_id', $user->id)->first();
                if($church_member == null){
                    redirect()->to('administration/pastors');
                    Notification::make()
                    ->success()
                    ->title('User Already Registered')
                    ->body('This phone number has already been registered.');

                    return $null_pastor;
                }else{
                    $pastor = Pastor::where('church_member_id', $church_member->id)->first();
                    return $pastor;
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
    
                $user->assignRole($data['title']);

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

                redirect()->to('administration/pastors');
                Notification::make()
                ->success()
                ->title('Failed To Create Pastor')
                ->body('Please try again, or contact the administrator for support');

                return $null_pastor;
            }

        
        }

    }

    protected function getCreatedNotification(): ?Notification
    {
        $church_district_churches = Church::where('church_district_id', auth()->user()->church_district_id)->pluck('id');
        if(Pastor::whereIn('church_assigned_id', $church_district_churches)->where('title', 'Senior Pastor')->where('status', 'active')->exists() || Pastor::whereIn('church_assigned_id', $church_district_churches)->where('title', 'Pastor')->where('status', 'active')->exists() || Pastor::whereIn('church_assigned_id', Church::where('parent_church', auth()->user()->church_id)->pluck('id'))->where('title', 'SubParish Pastor')->where('status', 'active')->exists()){
            return null;
        }else{
            if(User::where('phone', $this->data['phone'])->exists()){
                $user = User::where('phone', $this->data['phone'])->first();
                $church_member = ChurchMember::where('user_id', $user->id)->first();
                $pastor = Pastor::where('church_member_id', $church_member->id)->first();
                return Notification::make()
                        ->title($pastor->title." is already assigned.")
                        ->body("This Pastor has already been created as ".$pastor->title." in church ".$church_member->church->name)
                        ->success()
                        ->send();
            }else{
                return Notification::make()
                ->success()
                ->title('Pastor registered')
                ->body('The pastor has been created successfully.');
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // protected function beforeCreate(): void
    // {
    //     $church_district_churches = Church::where('church_district_id', auth()->user()->church_district_id)->pluck('id');
    //     if(Pastor::whereIn('church_assigned_id', $church_district_churches)->where('title', 'Senior Pastor')->where('status', 'active')->exists()){
    //         if(Pastor::whereIn('church_assigned_id', $church_district_churches)->where('title', 'Pastor')->where('status', 'active')->exists()){
    //             redirect()-to($this->getResource()::getUrl('index'));
    //             Notification::make()
    //             ->title('Church Already Assigned Pastors')
    //             ->body('This church is already assigned a pastors, you cant add any more pastors')
    //             ->warning()
    //             ->send();
    //         }else{
    //             redirect()->to($this->getResource()::getUrl('index'));
    //             Notification::make()
    //             ->title('Church Already Assigned Senior Pastor')
    //             ->body('This church is already assigned a Senior Pastor')
    //             ->warning()
    //             ->send();
    //         }
    //     }else{
    //         if(Pastor::whereIn('church_assigned_id', $church_district_churches)->where('title', 'Pastor')->where('status', 'active')->exists()){
    //             redirect()-to($this->getResource()::getUrl('index'));
    //             Notification::make()
    //             ->title('Church Already Assigned Pastor')
    //             ->body('This church is already assigned a pastor, you can only assign a senior pastor if available')
    //             ->warning()
    //             ->send();
    //         }
    //     }
    // }
}
