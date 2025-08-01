<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\ChurchMember;
use App\Models\Dinomination;
use App\Models\Diocese;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{
    public function index()
    {
        $dinominations = Dinomination::all();

        return response()->json($dinominations);
    }

    public function church($id)
    {
        $dioceses  = Diocese::with('dinomination')->where('dinomination_id', $id)->get(); // Get diocese that belongs to dinomination

        $church_details = [];

        foreach ($dioceses as $key => $diocese) {

            foreach ($diocese->churchDistrict as $key => $districts) {

                foreach ($districts->churches as $key => $church) {

                    $church_details [] = [
                        'church_id'           => $church->id,
                        'church_name'         => $church->name,
                        'church_type'         => $church->church_type,
                        'region'              => ['id' =>$church->region_id,    'name' => $church->region->name],
                        'district'            => ['id' => $church->district_id, 'name' => $church->district->name],
                        'ward'                => ['id' => $church->ward_id,     'name' => $church->ward->name],
                        'church_district'     => $church->churchDistrict->name,
                    ];

                }

            }

        }
        return response()->json($church_details);
    }

    public function search(Request $request)
    {
        $churches = [];

        $region   = Region::where('name','like', '%'.$request->name.'%')->pluck('id');
        $district = District::where('name', 'like', '%'.$request->name.'%')->pluck('id');
        $ward     = Ward::where('name', 'like', '%'.$request->name.'%')->pluck('id');

        $churches = Church::where('name', 'like', '%'.$request->name.'%')->orWhereIn('region_id', $region)
                                                        ->orWhereIn('district_id', $district)
                                                        ->orWhereIn('ward_id', $ward)->get();
        return response()->json($churches);
    }

    public function register(Request $request)
    {
        $validated = Validator::make($request->all(),[
           'phone'                  => 'required|regex:/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[\s\.\/0-9]*$/',
           'password'               => 'required|min:8',
           'church'                 => 'required',
           'dinomination'           => 'required',
        ]);
        if($validated->fails()){
            return response()->json([
                'errors'  => $validated->errors()
            ]);
        }else{
            if($validated->passes()){
                $data       =  $validated->safe()->only(['phone', 'password', 'dinomination', 'church']);
                $user_exist = User::where('phone', $data['phone'])->exists();
                if ($user_exist){
                    return response()->json([
                        'errorMessage' => 'User with this phone number already exist',
                        'status'       => 500
                    ]);
                }else{

                    User::create([
                        'phone'            => trim($data['phone']),
                        'password'         => Hash::make($data['password']),
                        'dinomination_id'  => $data['dinomination'],
                        'church_id'        => $data['church'],
                    ]);
                    return response()->json([
                        'message'      => 'Successful registered',
                        'status'       => '200'
                    ]);
                }
            }
        }
        return response()->json([
            'invalidRequestMessage' => 'Sorry, We can\'t process this request at the moment',
            'status'  => '404'
        ]);
    }


    public function registerChurchMembership(Request $request)
    {
        $validated = Validator::make($request->all(),[
            'phone'                  => 'regex:/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[\s\.\/0-9]*$/',
            'first_name'             => 'string',
            'middle_name'            => 'string',
            'surname'                => 'string',
            'email'                  => 'email:church_members,email',
            'marital_status'         => 'string',
            'date_of_birth'          => 'date',
            'spouse_name'            => 'string',
            'spouse_contact_no'      => 'regex:/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[\s\.\/0-9]*$/',
            'gender'                 => 'string',
            'region_id'              => 'integer',
            'district_id'            => 'integer',
            'ward_id'                => 'integer',
            'street'                 => 'string',
            'received_confirmation'  => 'boolean',
            'received_baptism'       => 'boolean',
            'card_no'                => 'integer',
            'block_no'               => 'string',
            'house_no'               => 'string',
        ]);

        if($validated->fails()){
            return response()->json([
                'errors'  => $validated->errors()
            ]);
        }else{
            if($validated->passes()){
                $data = $validated->safe()->only([
                        'phone',
                        'first_name',
                        'middle_name',
                        'surname',
                        'email',
                        'marital_status',
                        'date_of_birth',
                        'spouse_name',
                        'spouse_contact_no',
                        'gender',
                        'district_id',
                        'region_id',
                        'ward_id',
                        'street',
                        'house_no',
                        'card_no',
                        'received_baptism',
                        'received_confirmation',
                        'block_no'
                ]);
                $user = User::where('phone', $data['phone'])->first();

                if ($user){
                    $member = ChurchMember::where('user_id', $user->id)->exists();

                    if ($member){
                        return response()->json([
                            'errorMessage' => 'User with this phone number already exist',
                            'status'       => 500
                        ]);
                    }else{

                         $churchMember = new ChurchMember();

                         if ($data['marital_status'] != 'married') {
                            $data['spouse_contact_no'] = null;
                            $data['spouse_name']       = null;
                          }

                         $churchMember->first_name          = trim($data['first_name']);
                         $churchMember->middle_name         = trim($data['middle_name']);
                         $churchMember->surname             = trim($data['surname']);
                         $churchMember->user_id             = $user->id;
                         $churchMember->email               = isset($data['email']) ? trim($data['email']): null;
                         $churchMember->phone               = $user->phone;
                         $churchMember->gender              = $data['gender'];
                         $churchMember->date_of_birth       = trim($data['date_of_birth']);
                         $churchMember->card_no             = isset($data['card_no']) ? trim($data['card_no']): null;
                         $churchMember->church_id           = $user->church_id;
                         $churchMember->marital_status      = trim($data['marital_status']);
                         $churchMember->spouse_name         = trim($data['spouse_name']);
                         $churchMember->spouse_contact_no   = trim($data['spouse_contact_no']);
                         $churchMember->region_id           = $data['region_id'];
                         $churchMember->district_id         = $data['district_id'];
                         $churchMember->ward_id             = $data['ward_id'];
                         $churchMember->received_confirmation = isset($data['received_confirmation']) ? $data['received_confirmation']: null;
                         $churchMember->street              = isset($data['street']) ? trim($data['street']): null;
                         $churchMember->received_baptism    = isset($data['received_baptism']) ? $data['received_baptism']: null;
                         $churchMember->house_no            = isset($data['house_no']) ? trim($data['house_no']): null;
                         $churchMember->block_no            = isset($data['block_no']) ? trim($data['block_no']): null;

                         $churchMember->save();

                        return response()->json([
                            'name'         => $data['first_name'],
                            'message'      => 'Successful registered',
                            'status'       =>  200
                        ]);
                    }
                }else{
                    return response()->json([
                        'errorMessage'  => 'You can not be registered yet',
                        'status'        => 404
                    ]);
                }
            }
        }
        return response()->json([
            'invalidRequestMessage' => 'Sorry, We can\'t process this request at the moment',
            'status'  => 404
        ]);
    }

    public function region(Request $request)
    {
        $regions = Region::all();

        return response()->json($regions);
    }

    public function district($id)
    {
        $districts  = District::where('region_id', $id)->get();

        return response()->json($districts);
    }

    public function ward($id)
    {
        $wards = Ward::where('district_id', $id)->get();

        return response()->json($wards);
    }
}
