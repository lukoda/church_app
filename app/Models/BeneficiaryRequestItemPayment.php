<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class BeneficiaryRequestItemPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_mode',
        'request_type',
        'pay_date',
        'secret_key',
        'item_quantity_payed',
        'item_quantity_verified',
        'amount_payed',
        'amount_payed_verified',
        'account_provider',
        'bank_branch_name',
        'bank_transaction_id',
        'mobile_account_provider',
        'mobile_transaction_id',
        'receipt_picture',
        'item_id',
        'verification_status',
        'user_id'
    ];

    protected $casts = [
        'receipt_picture' => 'array'
    ];

    public function beneficiary_request_item(): BelongsTo
    {
        return $this->belongsTo(BeneficiaryRequestItemPledge::class, 'item_id');
    }
}
