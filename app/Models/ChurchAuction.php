<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChurchAuction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'log_offering',
        'auction_date',
        'auction_description',
        'status'
    ];
    

    public function log_offering(): BelongsTo
    {
        return $this->belongsTo(LogOffering::class, 'log_offering');
    }

    public function auction_items(): HasMany
    {
        return $this->hasMany(AuctionItem::class);
    }
}
