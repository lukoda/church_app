<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JumuiyaMember extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'church_member_id',
        'jumuiya_id',
        'date_registered',
        'status',
    ];

    public function church_member(): BelongsTo
    {
        return $this->belongsTo(ChurchMember::class);
    }
}
