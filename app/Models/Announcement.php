<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dinomination_id',
        'message',
        'status',
        'level',
        'all_dioceses',
        'diocese',
        'all_church_districts',
        'church_districts',
        'all_churches',
        'church',
        'all_sub_parishes',
        'sub_parish',
        'all_jumuiyas',
        'jumuiya',
        'documents',
        'begin_date',
        'duration',
        'end_date',
        'published_level',
        'published_member_church'
    ];

    protected $casts =  [
        'documents' => 'array',
        'all_dioceses' => 'boolean',
        'diocese' => 'array',
        'all_church_districts' => 'boolean',
        'church_districts' => 'array',
        'all_churches' => 'boolean',
        'church' => 'array',
        'all_sub_parishes' => 'boolean',
        'sub_parish' => 'array',
        'all_jumuiyas' => 'boolean',
        'jumuiya' => 'array',
        'published_member_church' => 'array'
    ];

    public function published_announcement() : HasOne
    {
        return $this->hasOne(AnnouncementPublications::class);
    }

}
