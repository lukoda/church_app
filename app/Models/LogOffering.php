<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\ChurchScope;

class LogOffering extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'adhoc_offering_id',
        'church_mass_id',
        'amount_committee',
        'amount_accountant',
        'has_auction',
        'date',
        'user_id',
        'approved_by',
        'church_id'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ChurchScope);
    }

    protected $casts = [
        'has_auction' => 'boolean'
    ];

    public function church_auction(): HasOne
    {
        return $this->hasOne(ChurchAuction::class, 'log_offering');
    }

    public function adhocOffering(): BelongsTo
    {
        return $this->belongsTo(AdhocOffering::class);
    }
}
