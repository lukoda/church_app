<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'region_id',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
