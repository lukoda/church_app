<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'church_member_id',
        'date_of_pledge',
        'amount_pledged',
        'amount_completed',
        'amount_remains',
        'status',
        'card_no',
        'full_name',
        'phone_no',
        'log_contribution_id'
    ];
}
