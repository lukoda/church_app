<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JumuiyaChairPerson extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_member_id',
        'jumuiya_id',
        'date_registered',
        'status',
    ];
}
