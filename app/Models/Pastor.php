<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pastor extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_member_id',
        'date_registered',
        'status',
        'title',
        'church_assigned_id',
        'comment'
    ];

    public function churchMember(): BelongsTo
    {
        return $this->belongsTo(ChurchMember::class);
    }

    public function pastorSchedules(): HasMany
    {
        return $this->hasMany(PastorSchedule::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_assigned_id');
    }
}
