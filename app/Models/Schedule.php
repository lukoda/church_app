<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'day',
        'from',
        'to',
        'status',
        'pastor_schedule_id',
        'max_members',
        'current_booked_members',
        'pending_approvals'
    ];


    protected $casts = [
        'status' => 'boolean'
    ];

    public function pastorSchedule(): BelongsTo
    {
        return $this->belongsTo(PastorSchedule::class);
    }
}
