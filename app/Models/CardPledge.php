<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Models\Scopes\ChurchScope;

class CardPledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_member_id',
        'church_id',
        'card_id',
        'card_no',
        'amount_pledged',
        'amount_completed',
        'amount_remains',
        'date_pledged',
        'created_by',
        'status'
    ];

    protected static function booted(): void
    {
        static::retrieved(function(CardPledge $card_pledge){
            CardPledge::whereYear('created_at', '!=', now()->year)->update([
                'status' => 'inactive'
            ]);
        });
        
        static::addGlobalScope(new ChurchScope);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function church_member(): BelongsTo
    {
        return $this->belongsTo(ChurchMember::class);
    }

    // protected static function booted(): void
    // {
    //     static::retrieved(function (CardPledge $card_pledge){
            
    //     });
    // }
}
