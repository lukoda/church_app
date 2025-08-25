<?php

namespace App\Http\Response;

use App\Filament\Resources\ChurchMemberResource;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Notifications\Notification;
use App\Models\User;
use App\Models\Church;

class RegistrationResponse implements Responsable
{
    public $phone;
    public $church_id;
    
    public function __construct($phone = null, $church_id = null)
    {
        $this->phone = $phone;
        $this->church_id = $church_id;
    }

    public function toResponse($request): RedirectResponse | Redirector
    {
        if(User::where('phone', $this->phone)->exists()){
            Notification::make()
            ->title('Phone Number already registered')
            ->body('The Phone Number is already registered in the system')
            ->warning()
            ->send();
            return redirect()->to('/admin/register');

        }else{ 
            if($this->church_id == null && Church::count() <= 0){
                Notification::make()
                ->title('No Registered church')
                ->body('Please contact your administrator, to register church')
                ->warning()
                ->send();
                return redirect()->to('/admin/register');
            }else if(auth()->user()->hasRole('Jumuiya Chairperson') || auth()->user()->hasRole('Jumuiya Accountant') || auth()->user()->hasRole('Committee Member') || auth()->user()->hasRole('Beneficiary')){
                return redirect()->to('admin');
            }else{
                // return redirect()->to(ChurchMemberResource::geturl('create'));
                return redirect()->to('admin/create-new-church-member');
            }
        }
    }
}