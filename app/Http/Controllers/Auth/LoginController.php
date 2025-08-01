<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ChurchMember;
use App\Models\User;
use Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone'    => ['required', 'regex:/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[\s\.\/0-9]*$/'],
            'password' => ['required']
        ]);

        // check if user exist
        $user   = User::where('phone', $credentials['phone'])->first();
        $member = ChurchMember::where('user_id', $user->id)->first();

        if ($credentials && $member) {
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('basic-token');
                return response()->json([
                    'success'     => 'Successful logged in',
                    'basic-token' => $token->plainTextToken,
                    'status'      => 200,
                    'user'        => ['phone' => $user->phone, 'data' => $member]
                ]);
            }else{
                return response()->json([
                    'message' => 'Wrong credentials'
                ]);
            }
        }elseif ($credentials) {
            if (Auth::attempt($credentials)) {
                $user  = Auth::user();
                $token = $user->createToken('basic-token');
                return response()->json([
                    'success'     => 'Successful logged in',
                    'basic-token' => $token->plainTextToken,
                    'status'      => 200,
                    'user'        => ['phone' => $user->phone, 'data' => null] ,
                ]);
            }
        }else {
            return response()->json([
                'errorMessage' => 'Credentials provided don\'t match any in our records',
                'status'       => 404
            ]);
        }
    }
}
