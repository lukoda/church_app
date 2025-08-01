<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\ChurchScope;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'men',
        'women',
        'children',
        'remark',
        // 'status',
        'church_id',
        'date',
        'church_mass_id'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ChurchScope);
    }
}
