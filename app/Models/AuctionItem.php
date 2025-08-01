<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuctionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'item_description',
        'amount_pledged',
        'amount_payed',
        'amount_remains',
        'is_church_member',
        'card_no',
        'name',
        'phone_no',
        'registered_on',
        'status',
        'created_by',
        'church_auction_id'
    ];

    public function church_auction(): BelongsTo
    {
        return $this->belongsTo(ChurchAuction::class);
    }

    public function auction_item_payments(): HasMany
    {
        return $this->hasMany(AuctionItemPayment::class);
    }
}
