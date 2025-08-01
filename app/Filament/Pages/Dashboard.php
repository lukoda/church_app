<?php

namespace App\Filament\Pages;


use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{

    public function getTitle(): string | Htmlable
    {
        return Auth::guard('web')->user()->churchMember ? 'Welcome '.'  '.auth()->user()->churchMember->surname.', '.auth()->user()->churchMember->first_name.' '.auth()->user()->churchMember->middle_name : "Welcome Guest";
    }
}
