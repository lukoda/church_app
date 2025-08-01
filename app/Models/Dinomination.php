<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dinomination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    public function dioceses(): HasMany
    {
        return $this->hasMany(Diocese::class);
    }
}

