<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Church extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pictures',
        'church_type',
        'parent_church',
        'church_location',
        'church_district_id',
        'region_id',
        'district_id',
        'ward_id',
        'pastors'
    ];

    protected $casts = [
        'pictures' => 'array',
        'pastors' => 'array'
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function churchDistrict(): BelongsTo
    {
        return $this->belongsTo(ChurchDistrict::class);
    }

    public function church_members(): HasMany
    {
        return $this->hasMany(ChurchMember::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }
    
    public function pastors(): HasMany
    {
        return $this->hasMany(Pastor::class, 'church_assigned_id');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }
}
