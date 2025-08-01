<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionItemPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_mode',
        'amount_payed',
        'date_registered',
        'description',
        'church_id',
        'auction_item_id',
        'registered_by',
        'account_provider',
        'bank_branch_name',
        'bank_transaction_id',
        'mobile_account_provider',
        'mobile_transaction_id',
        'receipt_picture',
        'verification_status'
    ];

    public function auction_item(): BelongsTo
    {
        return $this->belongsTo(AuctionItem::class);
    }
}
