<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\ChurchScope;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_name',
        'church_id',
        'card_description',
        // 'card_duration',
        // 'verse_for_card',
        'card_status',
        'card_color',
        'card_target',
        'minimum_target'
    ];


    protected static function booted(): void
    {
        static::addGlobalScope(new ChurchScope);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(CardPledge::class, 'card_type');
    }
}
