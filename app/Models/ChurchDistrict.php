<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class ChurchDistrict extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'all_wards',
        'regions',
        'districts',
        'wards',
        'diocese_id'
    ];

    protected $casts = [
        'all_wards' => 'array',
        'regions' => 'array',
        'districts' => 'array',
        'wards' => 'array'
    ];

    protected function churches(): HasMany
    {
        return $this->hasMany(Church::class);
    }

    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }
}
