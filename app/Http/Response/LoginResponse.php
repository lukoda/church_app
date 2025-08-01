<?php

namespace App\Http\Response;

use App\Filament\Resources\ChurchMemberResource;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use App\Models\CardPledge;
use App\Models\ChurchMember;
use App\Models\Church;
use App\Models\User;
use Filament\Notifications\Notification;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {

        if(auth()->user()->hasRole('Beneficiary')){
            return redirect()->intended(Filament::getUrl());
        }else{
            if(auth()->user()->churchMember){
                if(auth()->user()->churchMember->personal_details == 'complete' &&
                   auth()->user()->churchMember->address_details == 'complete' && 
                   auth()->user()->churchMember->spiritual_information == 'complete' &&
                   CardPledge::where('church_member_id', auth()->user()->churchMember->id)->exists()){
                    return redirect()->intended(Filament::getUrl());
                   }else{
                     if(auth()->user()->churchMember->personal_details == 'complete'){
                        if(auth()->user()->churchMember->personal_details == 'complete' &&
                        auth()->user()->churchMember->address_details == 'complete' && 
                        auth()->user()->churchMember->spiritual_information == 'complete' &&
                        ! CardPledge::where('church_member_id', auth()->user()->churchMember->id)->exists()){
                            Notification::make()
                                ->warning()
                                ->title('You haven\'t Pledged')
                                ->body('Please enter your card pledges to complete registration')
                                ->persistent()
                                ->send();
                            return redirect()->to('admin/members');
                        }else{
                            Notification::make()
                            ->warning()
                            ->title('You haven\'t comleted registration')
                            ->body('Please complete your registration to be registered to '.Church::whereId(auth()->user()->church_id)->pluck('name')[0])
                            ->persistent()
                            ->send();
    
                            return redirect()->to('admin/members');
                        }
                     }else{
                        if(! CardPledge::where('church_member_id', auth()->user()->churchMember->id)->exists()){
                            Notification::make()
                                ->warning()
                                ->title('You haven\'t Pledged')
                                ->body('Please enter your card pledges to complete registration')
                                ->persistent()
                                ->send();
                            return redirect()->to('admin/members');
                        }
                     }
                     }
    
            }else{
                Notification::make()
                    ->warning()
                    ->title('You haven\'t registered')
                    ->body('Please enter your details to be registred as church member to '.Church::whereId(auth()->user()->church_id)->pluck('name')[0])
                    ->persistent()
                    ->send();

                if(! auth()->user()->hasRole('Guest')){
                    $user = User::whereId(auth()->user()->id)->first();
                    $user->assignRole('Guest');
                }
                return redirect()->to("admin/create-new-church-member");
            }
        }

        
    }
}