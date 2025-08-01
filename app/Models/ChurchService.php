<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChurchService extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'church_id',
        'status',
    ];

    public function church_service_requests(): HasMany
    {
        return $this->hasMany(ChurchServiceRequest::class);
    }
}
