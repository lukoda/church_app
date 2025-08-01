<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChurchMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'surname',
        'full_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'date_registered',
        'nida_id',
        'passport_id',
        'picture',
        'is_NewMember',
        'card_no',
        'postal_code',
        'region_id',
        'district_id',
        'ward_id',
        'street',
        'block_no',
        'house_no',
        'personal_details',
        'address_details',
        'spiritual_information',
        'jumuiya_id',
        'received_confirmation',
        'confirmation_place',
        'confirmation_date',
        'received_baptism',
        'baptism_place',
        'baptism_date',
        'volunteering_in',
        'sacrament_participation',
        'previous_church',
        'marital_status',
        'spouse_id',
        'spouse_name',
        'spouse_contact_no',
        'education_level',
        'profession',
        'skills',
        'work_location',
        'user_id',
        'church_id',
        'physically_approved_by',
        'comment',
        'status',
    ];

    protected $casts = [
        'received_confirmation' => 'boolean',
        'received_baptism' => 'boolean',
        'is_NewMember' => 'boolean'
    ];

    public function dependants(): HasMany
    {
        return $this->hasMany(Dependant::class);
    }

    public function jumuiya(): BelongsTo
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pastor(): HasOne
    {
        return $this->hasOne(Pastor::class);
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(CardPledge::class);
    }

    public function jumuiyaMember(): HasOne
    {
        return $this->hasOne(JumuiyaMember::class);
    }
}
