<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BeneficiaryRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'beneficiary_type',
        'church_id',
        'amount',
        'purpose',
        'supporting_documents',
        'begin_date',
        'end_date',
        'status_approval',
        'request_visible_on',
        // 'approved_by',
        'frequency',
        'inactive_on',
        'weeks',
        'months',
        'status',
        'amount_threshold',
        'comment',
        'beneficiary_id',
        'registered_by'
    ];

    protected $casts = [
        'supporting_documents' => 'array',
    ];

    public function beneficiary_request_items(): HasMany
    {
        return $this->hasMany(BeneficiaryRequestItem::class);
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function request_amount_pledges(): HasMany
    {
        return $this->hasMany(BeneficiaryRequestItemPledge::class, 'request_item_id');
    }

    protected static function booted(): void
    {
        static::retrieved(function (BeneficiaryRequest $beneficiary_request){
            BeneficiaryRequest::where('church_id', auth()->user()->church_id)->where('end_date', '<', now())->update([
                'status' => 'Inactive'
            ]);
        });
    }
}
