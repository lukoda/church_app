<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Althinect\FilamentSpatieRolesPermissions\Concerns\HasSuperAdmin;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Authenticatable implements FilamentUser,HasName
{
    use HasFactory, HasRoles, HasSuperAdmin;

    protected $fillable = [
        'phone',
        'email_verified_at',
        'password',
        'church_level',
        'diocese_id',
        'church_district_id',
        'church_id',
        'dinomination_id'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true; 
    }

    public function getFilamentName(): string
    {
        return "Admin";
    }

    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class);
    }

    public function churchDistict(): BelongsTo
    {
        return $this->belongTo(ChurchDistrict::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }
}
