<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Committee extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'church_id',
        'church_member_id',
        'begin_date',
        'end_date',
        'serve_duration',
        'comment',
        'status',
    ];                                 

    protected static function booted(): void
    {
        static::retrieved(function (Committee $committe){
                Committee::where('end_date', '<', now())->update([
                    'status' => 'Inactive'
                ]);
                Committee::where('end_date', '<', now())->delete();
        });
    }
}
