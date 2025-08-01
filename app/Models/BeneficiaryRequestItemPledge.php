<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BeneficiaryRequestItemPledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_quantity_pledged',
        'item_quantity_complete',
        'amount_pledged',
        'amount_completed',
        'request_item_id',
        'pledged_item_id',
        'user_id',
        'payment_status'
    ];

    public function beneficiary_request(): BelongsTo
    {
        return $this->belongsTo(BeneficiaryRequest::class, 'request_item_id');
    }

    public function beneficiary_request_item(): BelongsTo
    {
        return $this->belongsTo(BeneficiaryRequestItem::class, 'pledged_item_id');
    }

    public function pledge_payments(): HasMany
    {
        return $this->hasMany(BeneficiaryRequestItemPayment::class, 'item_id');
    }
}
