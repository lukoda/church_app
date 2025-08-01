<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\ChurchScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class JumuiyaRevenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'jumuiya_id',
        'amount',
        'date_recorded',
        'jumuiya_attendance',
        'approval_status',
        'jumuiya_host_id'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ChurchScope);
    }

    public function jumuiya(): BelongsTo
    {
        return $this->belongsTo(Jumuiya::class);
    }
}
