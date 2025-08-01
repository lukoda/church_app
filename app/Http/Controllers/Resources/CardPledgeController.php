<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardPledge;
use App\Models\ChurchMember;
use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Type\Integer;
use Carbon\Carbon;

class CardPledgeController extends Controller
{
    public function cards(Request $request)
    {
        $cards = Card::where('church_id', $request->user()->church_id)->where('card_status', 'Active')->get(['id', 'church_id', 'card_name', 'card_description', 'card_color', 'card_target', 'minimum_target']);
        // $cards = Card::all();
        return response()->json($cards);
    }
    public function card_pledge(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'card_id'                => 'required',
            'card_no'                => 'required|integer',
            'amount_pledged'         => 'required|integer',
            // 'amount_completed'       => 'integer'
        ]);

        if ($validated->fails()) {
            return response()->json([
                'errors' => $validated->errors()
            ]);
        } elseif ($validated->passes()) {
            $validated_data      =  $validated->safe()->only(['card_id', 'card_no', 'amount_pledged']);
            $validated_data      = $validated->safe()->merge(['created_by' => $request->user()->id]);
            $created_by_user     = ChurchMember::whereUserId($request->user()->id)->first();

            // $amount_remains      = $validated_data['amount_pledged'] - $validated_data['amount_completed'];

            CardPledge::create([
                'card_no'          => $validated_data['card_no'],
                'card_id'          => $validated_data['card_id'],
                'amount_pledged'   => $validated_data['amount_pledged'],
                'church_member_id' => $created_by_user->id,
                'created_by'       => $request->user()->id,
                'amount_completed' => 0,
                'amount_remains'   => $validated_data['amount_pledged'],
                'date_pledged' => now(),
            ]);
            return response()->json([
                'message' => 'Pledge successful submitted',
                'status'  => 200
            ]);
        }
    }

    function get_card_pledge($card_id, $card_no)
    {
        $cardNo = $card_no;
        $cardId = $card_id;
        $cardPledge = CardPledge::where('card_id', $cardId)->where('card_no', $cardNo)->get();

        return response()->json($cardPledge);
    }

    function get_card_offerings($card_no, $card_type)
    {
        $offerings = Offering::where('card_no', $card_no)->where('card_type', $card_type)->get(['amount_offered', 'amount_registered_on']);
        return response()->json($offerings);
    }
}
