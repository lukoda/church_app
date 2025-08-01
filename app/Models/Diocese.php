<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Diocese extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'regions',
        'districts',
        'all_districts',
        'dinomination_id'
    ];

    protected $casts = [
        'regions' => 'array',
        'districts' => 'array',
        'all_districts' => 'array'
    ];

    public function churches(): HasMany
    {
        return $this->hasMany('Church::class');
    }

    public function dinomination(): BelongsTo
    {
        return $this->belongsTo(Dinomination::class);
    }

    public function churchDistrict() : HasMany
    {
        return $this->hasMany(ChurchDistrict::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }
}
