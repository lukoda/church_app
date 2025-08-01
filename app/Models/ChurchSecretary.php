<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChurchSecretary extends Model
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

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_assigned_id');
    }
}
