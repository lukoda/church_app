<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_member_id',
        'pastor_schedule_id',
        'booked_schedule',
        // 'purpose',
        'approval_status',
        'church_id'
    ];

}
