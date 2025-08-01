<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementPublications extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'sub_parish',
        'announcement_id',
        'jumuiya',
        'church_members',
        'church_id',
        'user_id'
    ];

    public function announcement() : BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }
}
