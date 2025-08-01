<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Althinect\FilamentSpatieRolesPermissions\Concerns\HasSuperAdmin;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements HasName, HasAvatar, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, HasSuperAdmin;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone',
        'password',
        'email_verified_at',
        'dinomination_id',
        'church_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
        'password'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getFilamentName(): string
    {
        return $this->churchMember ? "{$this->churchMember->first_name} {$this->churchMember->middle_name} {$this->churchMember->surname}" : 'Guest';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->churchMember ? "storage/{$this->churchMember->picture}" : Null;
    }

    public function churchMember(): HasOne
    {
        return $this->hasOne(ChurchMember::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }
    
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
