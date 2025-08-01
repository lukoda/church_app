<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\ChurchScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Offering extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_no',
        'card_type',
        'church_id',
        'amount_offered',
        'amount_registered_on',
        'created_by',
        'updated_by'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ChurchScope);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_type');
    }
}
