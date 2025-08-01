<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\ChurchScope;

class AdhocOffering extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'church_id',
        'status'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ChurchScope);
    }

    public function log_offerings(): HasMany
    {
        return $this->hasMany(LogOffering::class);
    } 

}
