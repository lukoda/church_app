<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Jumuiya extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'name',
        'region',
        'district',
        'postal_code',
        'ward',
        'street',
        'status'
    ];

    public function jumuiya_members(): HasMany
    {
        return $this->hasMany(ChurchMember::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(JumuiyaRevenue::class);
    }
}
