<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Scopes\ChurchScope;

#[ScopedBy([ChurchScope::class])]
class IntroductionNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_church_id',
        'to_church_id',
        'church_member_id',
        'title',
        'description',
        'date_requested',
        'sundays_on_leave',
        'date_of_return',
        'approved_on',
        'approval_status',
        'region_id',
        'district_id',
        'ward_id',
        'leaving_note',
        'status'
    ];

    protected static function booted(): void
    {
        static::retrieved(function(IntroductionNote $introNote){
            IntroductionNote::where('date_of_return', '<', now())->update([
                'status' => 'inactive'
            ]);
        });
    }
}
