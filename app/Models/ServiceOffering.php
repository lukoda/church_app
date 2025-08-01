<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOffering extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_no',
        'full_name',
        'phone',
        'church_member_id',
        'church_id',
        'log_contribution_id',
        'pledge_id',
        'amount',
        'status',
        'date'
    ];
}
