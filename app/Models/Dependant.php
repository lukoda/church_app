<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dependant extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'surname',
        'date_of_birth',
        'gender',
        'relationship',
        'church_member_id'
    ];

    public function churchMember(): BelongsTo
    {
        return $this->belongsTo(ChurchMember::class);
    }
}
