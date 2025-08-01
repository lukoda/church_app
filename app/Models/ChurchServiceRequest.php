<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\ChurchScope;

class ChurchServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_member_id',
        'full_name',
        'church_service_id',
        'date_requested',
        'message',
        'request_service_for',
        'is_churchMember',
        'requested_on_behalf_by',
        'jumuiya_chairperson_comment',
        'jumuiya_chairperson_approval_status',
        'approval_status',
        'approved_by',
        'approval_comment',
        'status'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ChurchScope);
    }

    protected $casts = [
        'is_churchMember' => 'boolean'
    ];

    public function ChurchService(): BelongsTo
    {
        return $this->belongsTo(ChurchService::class);
    }
}
