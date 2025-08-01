<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PastorSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'frequency',
        'church_id',
        'pastor_id'
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function pastor(): BelongsTo
    {
        return $this->belongsTo(Pastor::class);
    }

}
