<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'group_leader_name',
        'gender',
        'phone_no',
        'status',
        'payment_mode',
        'account_name',
        'account_provider',
        'account_no',
        'mobile_no',
        'mobile_account_name',
        'mobile_account_provider',
        'frequency',
        'church_id',
        'registered_by'
    ];

    protected $casts = [
        'payment_mode' => 'array',
        'account_name' => 'array',
        'account_no' => 'array',
        'mobile_no' => 'array',
        'mobile_account_name' => 'array',
        'account_provider' => 'array',
        'mobile_account_provider' => 'array'
    ];

    public function beneficiary_requests(): HasMany
    {
        return $this->hasMany(BeneficiaryRequest::class);
    }
}
