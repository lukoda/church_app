<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BeneficiaryRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item',
        'description',
        'quantity',
        'beneficiary_request_id'
    ];

    public function beneficiary_request(): BelongsTo
    {
        return $this->belongsTo(BeneficiaryRequest::class);
    }

    // public function beneficiary_request_item_payments(): HasMany
    // {
    //     return $this->hasMany(BeneficiaryRequestItemPayment::class, 'item_id');
    // }

    public function beneficiary_request_item_pledges(): HasMany
    {
        return $this->hasMany(BeneficiaryRequestItemPledge::class, 'pledged_item_id');
    }
}
