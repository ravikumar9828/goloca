<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\UserSendOtp;
use App\Mail\SendRegisterOtp;
use App\Models\UserModel;
use App\Models\ExpLevel;
use App\Models\CheckIn;
use App\Models\Country;
use App\Models\City;
use App\Models\Feed;
use App\Models\Follow;
use App\Models\UserLike;
use App\Models\Comment;
use App\Models\Group; 
use App\Models\CommentResponse;
use App\Models\FcmToken;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 
use Laravel\Sanctum\Sanctum;
use Mail;
use Auth;
use File;

use Carbon\Carbon;

class GolocaUserController extends Controller
{

    private function calculateStage($checkInPoints)
    {
        $stages = [
            0, 100, 1108.16, 2116.32, 3124.48, 4132.64, 5140.80, 6148.96, 7157.12, 8165.28, 9173.44, 10181.60, 11189.76, 12197.92, 13206.08, 14214.24, 15222.40, 16230.56, 17238.72, 18246.88, 19255.04, 20263.20, 21271.36, 22279.52, 23287.68, 24295.84, 25304.00, 26312.16, 27320.32, 28328.48, 29336.64, 30344.80, 31352.96, 32361.12, 33369.28, 34377.44, 35385.60, 36393.76, 37401.92, 38410.08, 39418.24, 40426.40, 41434.56, 42442.72, 43450.88, 44459.04, 45467.20, 46475.36, 47483.52, 48491.68, 49499.84, 50508.00, 51516.16, 52524.32, 53532.48, 54540.64, 55548.80, 56556.96, 57565.12, 58573.28, 59581.44, 60589.60, 61597.76, 62605.92, 63614.08, 64622.24, 65630.40, 66638.56, 67646.72, 68654.88, 69663.04, 70671.20, 71679.36, 72687.52, 73695.68, 74703.84, 75712.00, 76, 720.16, 77, 728.32, 78, 736.48, 79744.64, 80752.80, 81760.96, 82769.12, 83777.28, 84785.44, 85793.60, 86801.76, 87809.92, 88818.08, 89826.24, 90834.40, 91842.56, 92850.72, 93858.88, 94867.04, 95875.20, 96883.36, 97891.52, 98899.68
        ];

        foreach ($stages as $index => $threshold) {
            if ($checkInPoints <= $threshold) { 
                return $index;
            }
        }

        return count($stages) - 1;
    }
    public function userRegistration(Request $request)
    {
        $exists = UserModel::where('email', $request->email)->first();
        if (isset($exists)) {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required',
                'username'  => 'required',
                'email'     => 'required',
                'password'  => 'required|max:15',
                'confirm_password' => 'required|same:password',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
            }

            $otp = rand(100000, 999999);
            $users = UserModel::where('email', $exists->email)->update(['registration_otp' => $otp]);
            if ($users) {
                $credential = [
                    'title' => "Profile verify notification..",
                    'body'  => "Your profile create OTP is " . $otp,
                ];
            }
            $golocaUsers = UserModel::where('email', $request->email)->first();
            $golocaUsers->full_name = $request->full_name;
            $golocaUsers->username = $request->username;
            $golocaUsers->email = $request->email;
            $golocaUsers->password = Hash::make($request->password);
            $golocaUsers->status = 'active';
            $golocaUsers->update([$golocaUsers]);
            Mail::to($request->email)->send(new SendRegisterOtp($credential));
            return array('status' => 'Success', 'Messga' => 'Notification send successfully please check your email to verify..');
        } else {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required',
                'username' => 'required|unique:user_models,username',
                'email' => 'required|unique:user_models,email|email',
                'password' => 'required|max:15',
                'confirm_password' => 'required|same:password',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
            }

            $golocaUsers = new UserModel();
            $golocaUsers->full_name = $request->full_name;
            $golocaUsers->username = $request->username;
            $golocaUsers->email = $request->email;
            $golocaUsers->password = Hash::make($request->password);
            // $golocaUsers->plain_password = $request->password;
            $golocaUsers->status = 'active';
            $golocaUsers->save();
            if ($golocaUsers->id > 0) {
                $otp = rand(100000, 999999);
                $users = UserModel::where('email', $request->email)->update(['registration_otp' => $otp]);
                if ($users) {
                    $credential = [
                        'title' => "Profile verify notification..",
                        'body'  => "Your profile create OTP is " . $otp,
                    ];
                }
                Mail::to($request->email)->send(new SendRegisterOtp($credential));
                return array('status' => 'Success', 'Messga' => 'Notification send successfully please check your email to verify..');
            }
        }
    }

    public function verifyRegisterOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:user_models,email|email',
            'registration_otp' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $users = UserModel::where('email', $request->email)->first();
        $token = $users->createToken('Myapp')->plainTextToken;
        if ($users->registration_otp == $request->registration_otp) {
            UserModel::where('email', $request->email)->update(['registration_otp' => null, 'email_verified_at' => now()]);
            $data = UserModel::where('email', $request->email)->select('full_name', 'username', 'email', 'pro_img')->first();
            // if($data->pro_img){ 
            //     $data->img_url = asset('public/admin-assets/img/users/'.$data->pro_img);
            // }else{
            //     $data->img_url = null;
            // }

            if(!empty($request->token)){
                $notify = new FcmToken();
                $notify->user_id = $users->id;
                $notify->notify_token = $request->token;
                $notify->save();
            }
            $data->user_id = $users->id;
            return array('status' => 'Success', 'message' => 'Successfully registration', 'token' => $token, 'data' => $data);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Invalid OTP']);
        }
    }

    public function userLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:user_models,email|email',
            'password' => 'required|max:15',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $users = UserModel::where('email', $request->email)->where('email_verified_at', '!=', null)->first();
        if (!empty($users)) {
            if (Hash::check($request->password, $users->password)) {
                $token = $users->createToken('Myapp')->plainTextToken; 
                // dd($users->id);
                $data = UserModel::where('email', $request->email)->select('full_name', 'username', 'email', 'pro_img')->first();
                if($data->pro_img){
                    $data->img_url = asset('public/admin-assets/img/users/'.$data->pro_img);
                }else{
                    $data->img_url = null;
                }
                if(!empty($request->token)){
                    $notify = new FcmToken();
                    $notify->user_id = $users->id;
                    $notify->notify_token = $request->token;
                    $notify->save();
                }
                $data->user_id = $users->id;
                return array('status' => 'success', 'message' => "You are Loged In Successfully", 'token' => $token, 'data' => $data);
            } else {
                return array('status' => 'Failed', 'message' => 'Password not match');
            }
        } else {
            return array('status' => 'Failed', 'message' => 'Password not match');
        }
    }

    public function userLogout(Request $request)
    {
        $auth = auth('sanctum')->user()->id;
        $token = $request->bearerToken();
        if (!empty($token)) {
            $model = Sanctum::$personalAccessTokenModel;
            $accessToken = $model::findToken($token);
            $accessToken->delete();
            $fcmdelete = FcmToken::where('notify_token', $request->notify_token)->delete();
        } else {
            return array('status' => 'Failed', 'message' => 'Somthing went wrong');
        }
        return array('status' => 'Success', 'message' => 'Your successfully logout');
    }

    public function userSendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:user_models,email|email',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $otp = rand(100000, 999999);
        $users = UserModel::where('email', $request->email)->update(['otp' => $otp]);
        if ($users) {
            $mail_cred = [
                'title' => "Profile upload Notifications",
                'body' => "Your Profile Update OTP is " . $otp,
            ];
        }

        Mail::to($request->email)->send(new UserSendOtp($mail_cred));
        return array('status' => 'Success', 'message' => 'Otp send successfully to your email');
    }

    public function verifyOtpForgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:user_models,email|email',
            'otp' => 'required|exists:user_models,otp',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $users = UserModel::where('email', $request->email)->first();
        if ($users->otp == $request->otp) {
            UserModel::where('email', $request->email)->update(['otp' => null]);
        } else {
            return array('status' => 'Failed', 'message' => 'Otp not match');
        }
        return array('status' => 'Success', 'message' => 'Otp match successfully');
    }

    public function userChangPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:user_models,email|email',
            'password' => 'required|max:15',
            'confirm_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $passwords = UserModel::where('email', $request->email)->update(['password' => Hash::make($request->password)]);
        if ($passwords) {
            return array('status' => 'Success', 'message' => 'Password reset successfully');
        } else {
            return array('status' => 'Failed', 'message' => 'Somthing went wrong');
        }
    }

    public function user_profile_update(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'full_name' => 'required',
        //     'username' => 'required',
        // ]);
       
        $auth = auth('sanctum')->user()->id;
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                Rule::unique('user_models', 'username')->ignore($auth),
            ],
            'full_name' => ['required'],
           
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

       
        $users = UserModel::where('id', $auth)->first();
        $explevels = ExpLevel::where('user_id', $auth)->first();
        $exp = UserModel::where('id', $auth)->first()->check_in_points;
        if ($users) {
            if($users->pro_img_check == 0){
            if ($request->file('pro_img')) {
                $exp_points = 100;
                $data = new ExpLevel();
                $data->user_id = $auth; 
                $data->points  = $exp_points;
                $data->reason  = "Fist time user update our profile";
                $data->save(); 
                UserModel::where('id', $auth)->update(['check_in_points' => $exp + $exp_points]);
            }
        } 
        } else {
            return array('status' => 'Failed', 'message' => 'Somthing went wrong');
        }  

        $path = asset('public/admin-assets/img/users/' . $users->pro_img);
        $filename = '';
        if ($request->hasFile('pro_img')) {
            if (File::exists($path)) {
                File::delete($path);
            }
            $file = $request->file('pro_img');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/users/', $filename);
            $users['pro_img'] = $filename;
        }
        if($users->pro_img_check == 0){
            $users->pro_img_check = 1;
        }
        $users->full_name = $request->full_name;
        $users->username = $request->username;
        $users->bio = $request->bio;
        $users->gender = $request->gender;
        $users->phone = $request->phone;
        $users->dob = $request->dob;
        $users->instagram_links = $request->instagram_links;
        $users->facebook_links = $request->facebook_links;
        $users->tik_tok = $request->tik_tok;
        $users->type_of_traveler = $request->type_of_traveler;
        $users->linked_id = $request->linked_id;
        $users->discord = $request->discord;
        if($request->is_private == 1){
            Follow::where(['following_id' => $auth, 'status' => 0, 'accept' => 0])->update(['status' => 1, 'accept' => 1]); // ceck User Status and update hand to hand
            
            $users->is_private = $request->is_private;              
        }else{
            $users->is_private = $request->is_private;
        }
        $users->save(); 
        return array('status' => 'Success', 'message' => 'Profile update successfully');
       
    }

    public function show_user_profile()
    {

        $remaining = 0;
        $auth = auth('sanctum')->user()->id;
        $users = UserModel::where('id', $auth)->orderBy('id', 'desc')->select('id','is_private', 'full_name', 'username', 'subscription_verified', 'email', 'bio', 'gender', 'dob', 'pro_img', 'phone', 'instagram_links', 'facebook_links', 'tik_tok', 'linked_id', 'discord', 'check_in_points','type_of_traveler')->first();
        if ($users->check_in_points >= 1 && $users->check_in_points <= 100) { 
            $stage = 1;
            $remaining = $users->check_in_points - 1108.16;
        } elseif ($users->check_in_points >= 101.16 && $users->check_in_points <= 1108.16) { 
            $stage = 2;
            $remaining = $users->check_in_points - 2116.32;
        } elseif ($users->check_in_points >= 1019.16 && $users->check_in_points <= 2116.32) {
            $stage = 3;
            $remaining = $users->check_in_points - 3124.48;
        } elseif ($users->check_in_points >= 2117.32 && $users->check_in_points <= 3124.48) {
            $stage = 4;
            $remaining = $users->check_in_points - 4132.64;
        } elseif ($users->check_in_points >= 3125.48 && $users->check_in_points <= 4132.64) {
            $stage = 5;
            $remaining = $users->check_in_points - 5140.80;
        } elseif ($users->check_in_points >= 4133.64 && $users->check_in_points <= 5140.80) {
            $stage = 6;
            $remaining = $users->check_in_points - 6148.96;
        } elseif ($users->check_in_points >= 5141.80 && $users->check_in_points <= 6148.96) {
            $stage = 7;
            $remaining = $users->check_in_points - 7157.12;
        } elseif ($users->check_in_points >= 6149.96 && $users->check_in_points <= 7157.12) {
            $stage = 8;
            $remaining = $users->check_in_points - 7157.12;
        } else { 
            $stage = 0;
        }
        // dd($remaining);
        $remaining = abs($remaining);
        if($users->pro_img){
            $users['profile_url'] = asset('public/admin-assets/img/users/' . $users->pro_img);
        }else{
            $users['profile_url'] = null;
        }

        $countFollowing = Follow::where('user_id', $auth)->count();
        $countFollowers = Follow::where('following_id', $auth)->count();
        $users['followers_count'] = $countFollowers;
        $users['following_count'] = $countFollowing;
        $users['goloca_level'] = $this->calculateStage($users->check_in_points); 
        if ($remaining == " ") {
            $users['Need_for_next_level'] = (int)'100';
        } else {
            $users['Need_for_next_level'] = round($remaining, 2);
        }

        $showCountryHistory = CheckIn::where('user_id', $auth)->get();
        $totalCon = "195";             // this is total country counting only
        $complete = (count($showCountryHistory) * $totalCon) / 100;
        $users['world_discoverd']  =  intval($complete, '1');
        $country_visited  =  CheckIn::where('user_id', $auth)->select('country')->groupBy('country')->get();
        $users['country_visited']  =  count($country_visited);
        $users['amount_tript']     =  count($showCountryHistory);

        $usersss = UserModel::orderBy('id', 'desc');
        $country = Country::orderBy('id', 'desc');
        $city = City::orderBy('id', 'desc');
        $checkIn = CheckIn::orderBy('id', 'desc');

        $checkCountrys = $checkIn->where('user_id', $auth)->with('countryNames')->select('country')->groupBy('country')->limit('6')->get();
        foreach ($checkCountrys as $key => $checkCountry) {
            $cd = $checkCountry->country;
            $checkCountrys[$key]['countryNames']['countryImage'] = asset('public/admin-assets/img/country/country_badges/' . $checkCountry->countryNames->badges);
            $checkCountrys[$key]['countryVisiting'] = CheckIn::where('country', $checkCountry->country)->count();
            $totalConUsers = $usersss->count();
            $visitedUsersCountrys = CheckIn::where('country', $cd)->select('country', DB::raw('count(*) as total'))->groupBy('country')->get()->toArray();
            foreach ($visitedUsersCountrys as $userkey => $usersCountrys) {
                $checkCountrys[$key]['totalPercentageCountryUsers'] = $totalConUsers * $usersCountrys['total'] / 100;
            }
        }

        $checkCitys = CheckIn::where('user_id', $auth)->with('cityNames')->select('city')->groupBy('city')->limit('6')->get();
        foreach ($checkCitys as $key => $checkCity) {
            $ct = $checkCity->city;
            $checkCitys[$key]['cityVisiting'] = CheckIn::where('city', $checkCity['city'])->count();
            $checkCitys[$key]['cityNames']['cityimages'] = asset('public/admin-assets/img/city/' . $checkCity['cityNames']['city_badges']);
            $totalCityUsers = $usersss->count();
            $visitedUsersCitys = CheckIn::where('city', $ct)->select('city', DB::raw('count(*) as total'))->groupBy('city')->get()->toArray();
            foreach ($visitedUsersCitys as $visitkey => $usersCitysValue) {
                $checkCitys[$key]['totalPercentageCityUsers'] = ($totalCityUsers * $usersCitysValue['total']) / 100;
            }
        }

        $myfeed = array();
      
        $myfeeds = Feed::where('user_id', $auth)
        ->where('is_text', 'false') 
        ->where('group_id', null) 
        ->select('image')
        ->orderBy('id', 'desc')
        ->limit(6) 
        ->get()
        ->makeHidden(['feeds_url', 'image']);
        
        foreach ($myfeeds as $key => $feeds) {
            $decodes = json_decode($feeds->image, true);
            foreach ($decodes as $keyimage => $value) { 
                $myfeed[] = $value;
            }
        }
        
        $myfeedss = array();
        foreach ($myfeed as $keys => $values) { 
            $myfeedss[] = asset('public/admin-assets/img/user_feeds/' . $values); 
            if($keys >= 5){
            break;
            }
        }

       

 

        //============================= travel journy data ==========================================
        $pastTravel = CheckIn::where('user_id', $auth)->orderBy('id', 'desc')->with('countryNames', function ($query) {
            $query->select('id', 'country_name');
        })->with('cityNames', function ($querys) {
            $querys->select('id', 'city_name', 'city_badges');
        })->limit('6')->get();
        foreach ($pastTravel as $keysss => $past) {
            $pastTravel[$keysss]['cityNames']['city_url'] = asset('public/admin-assets/img/city/' . $past['cityNames']['city_badges']);
            //======================= geting between days start or end date =========================
            $formatted_dt1 = Carbon::parse($past->start_date);
            $formatted_dt2 = Carbon::parse($past->end_date);
            $date_diff = $formatted_dt1->diffInDays($formatted_dt2);
            $pastTravel[$keysss]['days'] = $date_diff;
            $data[] = $past->country;
            //======================= past travel users data =========================================
            $amountOfUsers = CheckIn::where('user_id', '!=', $auth)->where('city', $past->city)->pluck('user_id')->all();
            $pastTravel[$keysss]['x_more_users'] = count($amountOfUsers);
            // dd(count($amountOfUsers));
            $getUsers = UserModel::whereIn('id', $amountOfUsers)->select('id', 'full_name', 'username', 'pro_img')->limit('3')->get();
            foreach ($getUsers as $keyid => $getuser) {
                if($getuser->pro_img){
                    // $getuser->pro_img = asset('public/admin-assets/img/users/' . $getuser->pro_img);
                }else{
                    $getuser->pro_img = null;
                }
            }
            $pastTravel[$keysss]['pastUsersDetails'] = $getUsers;
        }


function myFunction(){
    $auth =Auth::guard('sanctum')->user()->id;
        $textfeeds = Feed::where('user_id', $auth)
        ->where('is_text', 'true') 
        ->where('group_id', null) 
        // ->select('image')
        ->orderBy('id', 'desc')
        ->limit(6) 
        ->get()
        ->makeHidden(['feeds_url', 'image']);
    if($textfeeds->toArray()){

        $textfeeds->transform(function($textfeeds){
            $user_id = $textfeeds->user_id;
            $user_id =  collect($user_id)->map(function($item) use($textfeeds){
                
                $user = UserModel::find($item);
                $userId = $user['id'];
                $textfeeds['is_private'] = $user['is_private'];
                $status = $textfeeds['is_private'];
                $textfeeds = $textfeeds->toArray();  
                    array_push($textfeeds,$status); 
                     return $textfeeds;  
            })->all();
            return $textfeeds;
        });
            // dd($textfeeds[0]['user_id']);
        foreach($textfeeds as $key => $item){ 
            $follower = Follow::where('following_id',Auth::guard('sanctum')->user()->id)->pluck('user_id')->toArray(); 
            $inArray = array_search(Auth::guard('sanctum')->user()->id,$follower); 
            $UserLike = UserLike::where('feed_id',$item['id'])->where('like_unlike','=','1')->get()->toArray();
            $is_Like = UserLike::where('user_id',Auth::guard('sanctum')->user()->id)->where('liked_user_id','=', $item['user_id'])->where('feed_id','=', $item['id'])->where('like_unlike','=','1')->get()->toArray();

            if($is_Like){
                $is_Like = 'true';
                $is_Like = filter_var($is_Like, FILTER_VALIDATE_BOOLEAN);
            }else{
                $is_Like = 'false';
                $is_Like = filter_var($is_Like, FILTER_VALIDATE_BOOLEAN);
            }
            $textfeeds[$key]['is_like'] = $is_Like;
            $UserComment = Comment::where('feed_id',$item['id'])->get()->toArray(); 
            $users = UserModel::where('id', $item['user_id'])->first()->toArray();
            // dd($users);
            $today = Carbon::today()->toDateString();
            
            $check_in = CheckIn::where('user_id',$item['user_id'])->orderBy('id', 'desc')->first();
            if($check_in){
                if ($check_in && ($check_in['start_date'] < $today || $check_in['end_date'] > $today)) {
                    $textfeeds[$key]['checkedIn_country'] = $check_in['country'];
                    $textfeeds[$key]['checkedIn_city'] = $check_in['city'];
                        // dd($check_in,$item['user_id']);
            }else{
                
                $textfeeds[$key]['checkedIn_country'] = 'not checkedIn';
                $textfeeds[$key]['checkedIn_city'] = 'not checkedIn';
            }
            }else{
                $textfeeds[$key]['checkedIn_country'] = 'not checkedIn';
                $textfeeds[$key]['checkedIn_city'] = 'not checkedIn';
            }
            
            
            $date = Carbon::parse($item['created_at']); 
            $formattedDate = $date->format('j M, Y');  
            $textfeeds[$key]['date'] =  $formattedDate;
            $textfeeds[$key]['user_details'] =  $users;
             
            if($item['is_private'] == '1'){  
                if(!$inArray){
                    // unset($textfeeds[$key]); 
                } 
            }   
            // dd($key,$UserLike,$UserComment);
            if($UserLike){  
                $textfeeds[$key]['like'] = (integer)count($UserLike); 
            }else{ 
                $textfeeds[$key]['like'] = (integer)'0';
            } 
            // dd($key);
            if($UserComment){  
                $textfeeds[$key]['Comment'] = (integer)count($UserComment); 
                }else{ 
                $textfeeds[$key]['Comment'] = (integer)'0';
            } 
             
           
        } 
        return $textfeeds;
    }else{
        $textfeeds = [];
        return $textfeeds;
    }
}




        return array('status' => 'Success', 'data' => $users, 'countrys' => $checkCountrys, 'citys' => $checkCitys, 'myFeeds' => $myfeedss, 'travel_journy_users' => $pastTravel
        ,'textFeeds'=>myFunction()
    );
    }

    public function removeProfilePicture()
    {
        $auth = auth('sanctum')->user()->id;
        $removes = UserModel::where('id', $auth)->first();
        $paths = public_path('/admin-assets/img/users/' . $removes->pro_img);
        if (File::exists($paths)) {
            File::delete($paths);
            $removes->update(['pro_img' => null]);
        }
        return array('status' => 'Success', 'message' => 'Picture remove successfully');
    }

    public function userList()
    {
        $auth = auth('sanctum')->user()->id;
        $following = Follow::where('user_id', $auth)->pluck('following_id')->all();
        $lists = UserModel::orderBy('id', 'desc')->select('id', 'is_private', 'username', 'pro_img', 'country', 'check_in_points')->get();
        foreach ($lists as $key => $list) {
            $lists[$key]['img_url'] = asset('public/admin-assets/img/users/' . $list->pro_img);
            if ($users->check_in_points >= 1 && $users->check_in_points <= 100) { 
                $stage = "1";
                $remaining = $users->check_in_points - 1108.16;
            } elseif ($list->check_in_points >= 101.16 && $list->check_in_points <= 1108.16) {
                $stage = "2";
                $remaining = $list->check_in_points - 2116.32;
            } elseif ($list->check_in_points >= 1019.16 && $list->check_in_points <= 2116.32) {
                $stage = "3";
                $remaining = $list->check_in_points - 3124.48;
            } elseif ($list->check_in_points >= 2117.32 && $list->check_in_points <= 3124.48) {
                $stage = "4";
                $remaining = $list->check_in_points - 4132.64;
            } elseif ($list->check_in_points >= 3125.48 && $list->check_in_points <= 4132.64) {
                $stage = "5";
                $remaining = $list->check_in_points - 5140.80;
            } elseif ($list->check_in_points >= 4133.64 && $list->check_in_points <= 5140.80) {
                $stage = "6";
                $remaining = $list->check_in_points - 6148.96;
            } elseif ($list->check_in_points >= 5141.80 && $list->check_in_points <= 6148.96) {
                $stage = "7";
                $remaining = $list->check_in_points - 7157.12;
            } elseif ($list->check_in_points >= 6149.96 && $list->check_in_points <= 7157.12) {
                $stage = "8";
                $remaining = $list->check_in_points - 7157.12;
            } else {
                $stage = "0";
            }
            
            $lists[$key]['goloca_level'] = $stage;
            if (in_array($list->id, $following)) {
                $lists[$key]['is_following'] = "true";
            } else {
                $lists[$key]['is_following'] = "false";
            }
        }
        return array('status' => 'Success', 'data' => $lists);
    }

    public function showOtherUserDeatils(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:user_models,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $auth = auth('sanctum')->user()->id;
        if ($request->user_id != $auth) {
            $remaining = " ";
            $users = UserModel::where('id', $request->user_id)->select('id','is_private', 'full_name', 'username', 'subscription_verified', 'email', 'bio', 'gender', 'dob', 'pro_img', 'phone', 'instagram_links', 'facebook_links', 'tik_tok', 'linked_id', 'discord', 'check_in_points','type_of_traveler')->orderBy('id', 'desc')->first();
            if ($users->check_in_points >= 1 && $users->check_in_points <= 100) { 
                $stage = 1;
                $remaining = $users->check_in_points - 1108.16;
            } elseif ($users->check_in_points >= 101.16 && $users->check_in_points <= 1108.16) {
                $stage = 2;
                $remaining = $users->check_in_points - 2116.32;
            } elseif ($users->check_in_points >= 1019.16 && $users->check_in_points <= 2116.32) {
                $stage = 3;
                $remaining = $users->check_in_points - 3124.48;
            } elseif ($users->check_in_points >= 2117.32 && $users->check_in_points <= 3124.48) {
                $stage = 4;
                $remaining = $users->check_in_points - 4132.64;
            } elseif ($users->check_in_points >= 3125.48 && $users->check_in_points <= 4132.64) {
                $stage = 5;
                $remaining = $users->check_in_points - 5140.80;
            } elseif ($users->check_in_points >= 4133.64 && $users->check_in_points <= 5140.80) {
                $stage = 6;
                $remaining = $users->check_in_points - 6148.96;
            } elseif ($users->check_in_points >= 5141.80 && $users->check_in_points <= 6148.96) {
                $stage = 7;
                $remaining = $users->check_in_points - 7157.12;
            } elseif ($users->check_in_points >= 6149.96 && $users->check_in_points <= 7157.12) {
                $stage = 8;
                $remaining = $users->check_in_points - 7157.12;
            } else {
                $stage = 0;
            }
             
            $remaining = abs((int)$remaining);
     
            $checkFollow = Follow::where('user_id', $auth)->where('following_id', $request->user_id)->first();
            if ($checkFollow) {
                $users['is_following'] = $checkFollow->status;
            } else {
                $users['is_following'] = null;
            }

            if($users->pro_img){
                $users['profile_url'] = asset('public/admin-assets/img/users/' . $users->pro_img);
                $users['profile_url'] = asset('public/admin-assets/img/users/' . $users->pro_img);
            }else{
                $users['profile_url'] = null;
            }

            $countFollowing = Follow::where('user_id', $request->user_id)->count();
            $countFollowers = Follow::where('following_id', $request->user_id)->count();
            $users['followers_count'] = $countFollowers;
            $users['following_count'] = $countFollowing;
            $users['goloca_level'] = $stage;
            if ($remaining == " ") {
                $users['Need_for_next_level'] = (int)'100';
            } else {
                $users['Need_for_next_level'] = round($remaining, 2);
            }

            $showCountryHistory = CheckIn::where('user_id', $request->user_id)->get();
            $totalCon = "195";
            $complete = (count($showCountryHistory) * $totalCon) / 100;
            $users['world_discoverd']  =  intval($complete, '1');
            $country_visited  =  CheckIn::where('user_id', $request->user_id)->select('country')->groupBy('country')->get();
            $users['country_visited']  =  count($country_visited);
            $users['amount_tript']     =  count($showCountryHistory);
            // dd($users->is_private);
           
            if ($users->check_in_points >= 1 && $users->check_in_points <= 100) { 
                $stage = 1;
                $remaining = $users->check_in_points - 1108.16;
            } elseif ($users->check_in_points >= 101.16 && $users->check_in_points <= 1108.16) {
                    $stage = 2;
                    $remaining = $users->check_in_points - 2116.32;
                } elseif ($users->check_in_points >= 1019.16 && $users->check_in_points <= 2116.32) {
                    $stage = 3;
                    $remaining = $users->check_in_points - 3124.48;
                } elseif ($users->check_in_points >= 2117.32 && $users->check_in_points <= 3124.48) {
                    $stage = 4;
                    $remaining = $users->check_in_points - 4132.64;
                } elseif ($users->check_in_points >= 3125.48 && $users->check_in_points <= 4132.64) {
                    $stage = 5;
                    $remaining = $users->check_in_points - 5140.80;
                } elseif ($users->check_in_points >= 4133.64 && $users->check_in_points <= 5140.80) {
                    $stage = 6;
                    $remaining = $users->check_in_points - 6148.96;
                } elseif ($users->check_in_points >= 5141.80 && $users->check_in_points <= 6148.96) {
                    $stage = 7;
                    $remaining = $users->check_in_points - 7157.12;
                } elseif ($users->check_in_points >= 6149.96 && $users->check_in_points <= 7157.12) {
                    $stage = 8;
                    $remaining = $users->check_in_points - 7157.12;
                } else {
                    $stage = 0;
                }
                $remaining = abs((int)$remaining);
                $users['profile_url'] = asset('public/admin-assets/img/users/' . $users->pro_img);
                if($users->pro_img){
                    $users['profile_url'] = asset('public/admin-assets/img/users/' . $users->pro_img);
                }else{
                    $users['profile_url'] = null;
                }
                $users['goloca_level'] = $stage;
                if ($remaining == " ") {
                    $users['Need_for_next_level'] = (int)'100';
                } else {
                    $users['Need_for_next_level'] = round($remaining, 2);
                }

                $showCountryHistory = CheckIn::where('user_id', $request->user_id)->get();
                $totalCon = "195";
                $complete = (count($showCountryHistory) * $totalCon) / 100;
                $users['world_discoverd']  =  intval($complete, '1');
                $country_visited  =  CheckIn::where('user_id', $request->user_id)->select('country')->groupBy('country')->get();
                $users['country_visited']  =  count($country_visited);
                $users['amount_tript']     =  count($showCountryHistory);

                $usersss = UserModel::orderBy('id', 'desc');
                $country = Country::orderBy('id', 'desc');
                $city = City::orderBy('id', 'desc');
                $checkIn = CheckIn::orderBy('id', 'desc');;

                $checkCountrys = $checkIn->where('user_id', $request->user_id)->with('countryNames')->select('country')->groupBy('country')->limit('6')->get();
                foreach ($checkCountrys as $key => $checkCountry) {
                    $cd = $checkCountry->country;
                    $checkCountrys[$key]['countryNames']['countryImage'] = asset('public/admin-assets/img/country/country_badges/' . $checkCountry->countryNames->badges);
                    $checkCountrys[$key]['countryVisiting'] = CheckIn::where('country', $checkCountry->country)->count();
                    $totalConUsers = $usersss->count();
                    $visitedUsersCountrys = CheckIn::where('country', $cd)->select('country', DB::raw('count(*) as total'))->groupBy('country')->get()->toArray();
                    foreach ($visitedUsersCountrys as $userkey => $usersCountrys) {
                        $checkCountrys[$key]['totalPercentageCountryUsers'] = $totalConUsers * $usersCountrys['total'] / 100;
                    }
                }

                $checkCitys = CheckIn::where('user_id', $request->user_id)->with('cityNames')->select('city')->groupBy('city')->limit('6')->get();
                foreach ($checkCitys as $key => $checkCity) {
                    $ct = $checkCity->city;
                    $checkCitys[$key]['cityVisiting'] = CheckIn::where('city', $checkCity['city'])->count();
                    $checkCitys[$key]['cityNames']['cityimages'] = asset('public/admin-assets/img/city/' . $checkCity['cityNames']['city_badges']);
                    $totalCityUsers = $usersss->count();
                    $visitedUsersCitys = CheckIn::where('city', $ct)->select('city', DB::raw('count(*) as total'))->groupBy('city')->get()->toArray();
                    foreach ($visitedUsersCitys as $visitkey => $usersCitysValue) {
                        $checkCitys[$key]['totalPercentageCityUsers'] = ($totalCityUsers * $usersCitysValue['total']) / 100;
                    }
                }

                $myfeed = array();
                // $myfeeds = Feed::where('user_id', $request->user_id)->select('image')->orderBy('id', 'desc')->get()->makeHidden('feeds_url',)->makeHidden('image');
                $myfeeds = Feed::where('user_id', $request->user_id)
                ->where('is_text', 'false')
                ->select('image')
                ->orderBy('id', 'desc')
                ->limit(6) 
                ->get()
                ->makeHidden(['feeds_url', 'image']);
                foreach ($myfeeds as $key => $feeds) {
                    $decodes = json_decode($feeds->image, true);
                    foreach ($decodes as $keyimage => $value) {
                        $myfeed[] = $value;
                    }
                }
                $myfeedss = array();
                foreach ($myfeed as $keys => $values) { 
                    $myfeedss[] = asset('public/admin-assets/img/user_feeds/' . $values); 
                    if($keys >= 5){
                        break;
                        }
                }

                $pastTravel = CheckIn::where('user_id', $request->user_id)->orderBy('id', 'desc')->with('countryNames', function ($query) {
                    $query->select('id', 'country_name');
                })->with('cityNames', function ($querys) {
                    $querys->select('id', 'city_name', 'city_badges');
                })->limit('6')->get();
                foreach ($pastTravel as $keysss => $past) {
                    $pastTravel[$keysss]['cityNames']['city_url'] = asset('public/admin-assets/img/city/' . $past['cityNames']['city_badges']);
                    //======================= geting between days start or end date =========================
                    $formatted_dt1 = Carbon::parse($past->start_date);
                    $formatted_dt2 = Carbon::parse($past->end_date);
                    $date_diff = $formatted_dt1->diffInDays($formatted_dt2);
                    $pastTravel[$keysss]['days'] = $date_diff;
                    $data[] = $past->country;
                    //======================= past travel users data =========================================
                    $amountOfUsers = CheckIn::where('user_id', '!=', $request->user_id)->where('city', $past->city)->pluck('user_id')->all();
                    $pastTravel[$keysss]['x_more_users'] = count($amountOfUsers);
                    // dd(count($amountOfUsers));
                    $getUsers = UserModel::whereIn('id', $amountOfUsers)->select('id', 'full_name', 'username', 'pro_img')->get();
                    foreach ($getUsers as $keyid => $getuser) {
                        // $getUsers[$keyid]['profileUrls'] = asset('public/admin-assets/img/users/' . $getuser->pro_img);
                        // $getUsers[$keyid]['x_more_users'] = count($getUsers);
                        if($getuser->pro_img){
                            // $getuser->pro_img = asset('public/admin-assets/img/users/' . $getuser->pro_img);
                        }else{
                            $getuser->pro_img = null;
                        }
                    }
                    $pastTravel[$keysss]['pastUsersDetails'] = $getUsers;
                }
                 
                function myFunction2($user_id){
                    $auth =Auth::guard('sanctum')->user()->id;
                    $textfeeds = Feed::where('user_id', $user_id)
                    ->where('is_text', 'true')
                    // ->select('image')
                    ->where('group_id', null)  
                    ->orderBy('id', 'desc')
                    ->limit(6) 
                    ->get()
                    ->makeHidden(['feeds_url', 'image']);
                    if($textfeeds->toArray()){
               
                        $textfeeds->transform(function($textfeeds){
                            $user_id = $textfeeds->user_id;
                            $user_id =  collect($user_id)->map(function($item) use($textfeeds){
                                
                                $user = UserModel::find($item);
                                $userId = $user['id'];
                                $textfeeds['is_private'] = $user['is_private'];
                                $status = $textfeeds['is_private'];
                                $textfeeds = $textfeeds->toArray();  
                                    array_push($textfeeds,$status); 
                                     return $textfeeds;  
                            })->all();
                            return $textfeeds;
                        });
                            // dd($textfeeds[0]['user_id']);
                        foreach($textfeeds as $key => $item){ 
                            $follower = Follow::where('following_id',Auth::guard('sanctum')->user()->id)->pluck('user_id')->toArray(); 
                            $inArray = array_search(Auth::guard('sanctum')->user()->id,$follower); 
                            $UserLike = UserLike::where('feed_id',$item['id'])->where('like_unlike','=','1')->get()->toArray();
                            $is_Like = UserLike::where('user_id',Auth::guard('sanctum')->user()->id)->where('liked_user_id','=', $item['user_id'])->where('feed_id','=', $item['id'])->where('like_unlike','=','1')->get()->toArray();
                
                            if($is_Like){
                                $is_Like = 'true';
                                $is_Like = filter_var($is_Like, FILTER_VALIDATE_BOOLEAN);
                            }else{
                                $is_Like = 'false';
                                $is_Like = filter_var($is_Like, FILTER_VALIDATE_BOOLEAN);
                            }
                            $textfeeds[$key]['is_like'] = $is_Like;
                            $UserComment = Comment::where('feed_id',$item['id'])->get()->toArray(); 
                            $users = UserModel::where('id', $item['user_id'])->first()->toArray();
                            // dd($users);
                            $today = Carbon::today()->toDateString();
                            
                            $check_in = CheckIn::where('user_id',$item['user_id'])->orderBy('id', 'desc')->first();
                            if($check_in){
                                if ($check_in && ($check_in['start_date'] < $today || $check_in['end_date'] > $today)) {
                                    $textfeeds[$key]['checkedIn_country'] = $check_in['country'];
                                    $textfeeds[$key]['checkedIn_city'] = $check_in['city'];
                                    // dd($check_in,$item['user_id']);
                         }else{
                            
                            $textfeeds[$key]['checkedIn_country'] = 'not checkedIn';
                            $textfeeds[$key]['checkedIn_city'] = 'not checkedIn';
                         }
                        }else{
                            $textfeeds[$key]['checkedIn_country'] = 'not checkedIn';
                            $textfeeds[$key]['checkedIn_city'] = 'not checkedIn';
                        }
                            $date = Carbon::parse($item['created_at']); 
                            $formattedDate = $date->format('j M, Y');  
                            $textfeeds[$key]['date'] =  $formattedDate;
                            $textfeeds[$key]['user_details'] =  $users;
                             
                            if($item['is_private'] == '1'){  
                                if(!$inArray){
                                    // unset($textfeeds[$key]); 
                                } 
                            }   
                
                            if($UserLike){  
                                $textfeeds[$key]['like'] = (integer)count($UserLike); 
                                }else{ 
                                $textfeeds[$key]['like'] = (integer)'0';
                            } 
                
                            if($UserComment){  
                                $textfeeds[$key]['Comment'] = (integer)count($UserComment); 
                                }else{ 
                                $textfeeds[$key]['Comment'] = (integer)'0';
                            }  
                        } 
                        return $textfeeds;
                    }else{
                        
                        return [];
                    }
                } 
        

                return array('status' => 'Success', 'users' => $users, 'countrys' => $checkCountrys, 'citys' => $checkCitys, 'myFeeds' => $myfeedss, 'travel_journy_users' => $pastTravel, 'textFeeds'=>myFunction2($request->user_id));
            
        } else {
            return array('status' => 'Failed', 'message' => 'Can not show login account');
        }
    }

    public function user_delete_account()
    {
        $auth = auth('sanctum')->user()->id;
        $delete = UserModel::where('id', $auth)->delete();
        if ($delete) {
            return array('status' => 'Success', 'message' => 'Account deleted successfully');
        } else {
            return array('status' => 'Failed', 'message' => 'Somthing went wrong');
        }
    }

    public function localevelStage()
    {
        $auth = auth('sanctum')->user()->id;
        $users = UserModel::where('id', $auth)->orderBy('id', 'desc')->first()->check_in_points;
        if ($users >= 100) {
            dd('ok');
        } else {
            dd('notok');
        }
        // for($u = $users; $u <= 10; $u++){
        //     for($j = $u; $j <= 98899.68; $j+=1108.16){
        //         echo $j.' ';
        //     }
        //     echo '<br>';
        // }
    }

    public function seeAllCountries(Request $request)
    {
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $auth = auth('sanctum')->user()->id;
        $showCountryHistory = CheckIn::where('user_id', $auth)->select('country')->with('countryNames')->groupBy('country')->take($per_page_limit)->skip($offset)->get();
        foreach ($showCountryHistory as $key => $country) {
            $cd = $country->country;
            $showCountryHistory[$key]['countryNames']['countryImage'] = asset('public/admin-assets/img/country/country_badges/' . $country->countryNames->badges);
            $showCountryHistory[$key]['countryVisiting'] = CheckIn::where('country', $cd)->count();
            $totalConUsers =  UserModel::orderBy('id', 'desc')->count();
            $visitedUsersCountrys = CheckIn::where('country', $cd)->select('country', DB::raw('count(*) as total'))->groupBy('country')->get()->toArray();
            foreach ($visitedUsersCountrys as $userkey => $usersCountrys) {
                $showCountryHistory[$key]['totalPercentageCountryUsers'] = $totalConUsers * $usersCountrys['total'] / 100;
            }
        }
        return array('status' => 'Success', 'data' => $showCountryHistory);
    }

    public function seeAllCity(Request $request)
    {
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $auth = auth('sanctum')->user()->id;
        $showCitys = CheckIn::where('user_id', $auth)->select('city')->with('cityNames')->groupBy('city')->take($per_page_limit)->skip($offset)->get();
        foreach ($showCitys as $key => $showCity) {
            $ct = $showCity->city;
            $showCitys[$key]['cityNames']['cityimgs'] = asset('public/admin-assets/img/city/' . $showCity['cityNames']['city_badges']);
            $showCitys[$key]['cityVisiting'] = CheckIn::where('city', $ct)->count();
            $totalCityUsers = UserModel::orderBy('id', 'desc')->count();
            $visitedUsersCitys = CheckIn::where('city', $ct)->select('city', DB::raw('count(*) as total'))->groupBy('city')->get()->toArray();
            foreach ($visitedUsersCitys as $visitkey => $usersCitysValue) {
                $showCitys[$key]['totalPercentageCityUsers'] = ($totalCityUsers * $usersCitysValue['total']) / 100;
            }
        }
        return array('status' => 'Success', 'data' => $showCitys);
    }

    public function seeAllImagesVideos(Request $request)
    {
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $auth = auth('sanctum')->user()->id;
        $myfeed = array();
        $myfeeds = Feed::where('user_id', $auth)->where('is_text', 'false')->orderBy('id', 'desc')->take($per_page_limit)->skip($offset)->get()->makeHidden('image');
        $islikeds = UserLike::where("user_id", $auth)->pluck('feed_id')->toArray();
        // dd($islikeds);
        foreach ($myfeeds as $key => $feeds) {
            $today = now();
            $formatted_dt1 = Carbon::parse($feeds->created_at);
            $formatted_dt2 = Carbon::parse($today);
            $date_diff = $formatted_dt1->diffInDays($formatted_dt2);
            $myfeeds[$key]['date_diff'] = $date_diff;
            $myfeeds[$key]['like'] = UserLike::where('feed_id', $feeds->id)->count();
            $myfeeds[$key]['Comment'] = Comment::where('feed_id', $feeds->id)->count();
            $decodes = json_decode($feeds->image, true);
            foreach ($decodes as $value) {
                $myfeed[] = asset('public/admin-assets/img/user_feeds/' . $value);
            }

            if(in_array($feeds->id, $islikeds)){
                $myfeeds[$key]['isLiked'] = true; 
            }else{
                $myfeeds[$key]['isLiked'] = false; 
            }

            $date = Carbon::parse($feeds->created_at); 
            $formattedDate = $date->format('j M, Y'); 
            $myfeeds[$key]['date'] =  $formattedDate;

            $user_detailsss = UserModel::where('id', $auth)->get();
            $user_detailsss->each(function ($single){
                if($single->pro_img){
                    $single->pro_img = asset('public/admin-assets/img/users/' . $single->pro_img);
                }else{
                    $single->pro_img = null;
                }
            });
            $myfeeds[$key]['user_details'] = $user_detailsss;
        }
        return array('status' => 'Success', 'data' => $myfeeds);
    }

    public function seeAllTravelJourny(Request $request)
    {
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;
        $usersss = UserModel::orderBy('id', 'desc');

        $auth = auth('sanctum')->user()->id;
        $pastTravel = CheckIn::where('user_id', $auth)->orderBy('id', 'desc')->with('countryNames', function ($query) {
            $query->select('id', 'country_name');
        })->with('cityNames', function ($querys) {
            $querys->select('id', 'city_name', 'city_badges');
        })->take($per_page_limit)->skip($offset)->get();
        foreach ($pastTravel as $keysss => $past) {
            $pastTravel[$keysss]['cityNames']['city_url'] = asset('public/admin-assets/img/city/' . $past['cityNames']['city_badges']);
            //======================= geting between days start or end date =========================
            $formatted_dt1 = Carbon::parse($past->start_date);
            $formatted_dt2 = Carbon::parse($past->end_date);
            $date_diff = $formatted_dt1->diffInDays($formatted_dt2);
            $pastTravel[$keysss]['days'] = $date_diff;
            $data[] = $past->country;
            //======================= past travel users data =========================================
            $amountOfUsers = CheckIn::where('user_id', '!=', $auth)->where('city', $past->city)->pluck('user_id')->all();
            $pastTravel[$keysss]['x_more_users'] = count($amountOfUsers);
            $getUsers = UserModel::whereIn('id', $amountOfUsers)->select('id', 'full_name', 'username', 'pro_img')->get();
            foreach ($getUsers as $keyid => $getuser) {
                if($getuser->pro_img){
                    $getuser->pro_img = asset('public/admin-assets/img/users/' . $getuser->pro_img);
                }else{
                    $getuser->pro_img = null;
                }
            }
            $pastTravel[$keysss]['usersDetails'] = $getUsers;
        }
        return array('status' => 'Success', 'data' => $pastTravel);
    }


    public function user_block(Request $request){
        $validator = Validator::make($request->all(), [
            'blocked_user_id'       => 'required',
            'is_block'       => 'required',
             
        ]); 
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }
        $auth = auth('sanctum')->User()->id;
        $user = UserModel::find($auth);
        if(!$user){
            return response()->json(['status' => 'Failed', 'message' => 'user_id not find']);
        }
    if($request->is_block == '1'){
         if($user->is_block === null){
        $autharr = [(string)$request->blocked_user_id];
        $user->is_block = json_encode($autharr, JSON_FORCE_OBJECT);
        $user->save();
        return response()->json(['status' => 'Success', 'message' => 'user block successfully']);
         }
        $is_block = json_decode($user->is_block,true);
       $check_arr = array_search($request->blocked_user_id,$is_block);
       if($check_arr !== false){
        return response()->json(['status' => 'Failed', 'message' => 'this user already blocked']);
       }
        array_push($is_block,$request->blocked_user_id);
        $is_block = array_values($is_block);
        $user->is_block = json_encode($is_block, JSON_FORCE_OBJECT); 
        $user->save();
        return response()->json(['status' => 'Success', 'message' => 'user block successfully']);

    }
    else if($request->is_block == '0'){
        if($user->is_block === null){
            return response()->json(['status' => 'Failed', 'message' => 'you have not any blocked user']);
        }
        $is_block = json_decode($user->is_block,true);
        $check_arr = array_search($request->blocked_user_id,$is_block);
        if($check_arr === false){
         return response()->json(['status' => 'Failed', 'message' => 'user not blocked by you']);
        }
        unset($is_block[$check_arr]);
        $count = count($is_block);
       
        if($count == '0'){
            $user->is_block = null; 
            $user->save();
            return response()->json(['status' => 'Success', 'message' => 'user un-block successfully']);

        }else{
            $fresh = array_values($is_block);
            $user->is_block = json_encode($fresh, JSON_FORCE_OBJECT); 
            $user->save();
            return response()->json(['status' => 'Success', 'message' => 'user un-block successfully']);
        }  
    }else{
        return response()->json(['status' => 'Success', 'message' => 'Oops Something else wrong']); 
    } 
    }
    public function user_block_list(){
        $auth = auth('sanctum')->User()->id;
        $user = UserModel::find($auth);
        if(!$user){
            return response()->json(['status' => 'Failed', 'message' => 'user_id not find']);
        }
        if($user->is_block === null){
            return response()->json(['status' => 'Success', 'data' => []]);

        }
        $is_block = json_decode($user->is_block,true);
        $userstagings = $is_block;
        $usersData = [];
        if (is_array($userstagings)) {


            foreach ($userstagings as $key => $taging) {
                $user = UserModel::where('id', $taging)->select('id', 'username','subscription_verified','is_private','isonline', 'full_name', 'pro_img')->first();
          
           
                $img_url = '';
                if ($user) {
                if($user->pro_img != null){
                    // dd(request()->route()->getName());
                    $img_url = asset('public/admin-assets/img/users/'.$user->pro_img);
                }else{
                    $img_url = null;
                }
                
                
                if ($user) {
                    // dd('ok');
                    $usersData[] = [
                        'id' => $user->id,
                        'username' => $user->username,
                        'full_name' => $user->full_name,
                        'pro_img' => $img_url,
                        'is_private' => $user->is_private,
                        'subscription_verified' => $user->subscription_verified,
                    ];
                }
            } 
        } 
    }  
        return response()->json(['status' => 'Success', 'data' => $usersData]); 
    }
    public function showEarnBadges(Request $request)
    { 
        $auth = auth('sanctum')->user()->id; 
        // user details for leaderbord 
        $leaderbord = UserModel::where('check_in_points', '!=', '0')->orderBY('check_in_points', 'desc')->select('id', 'full_name', 'username', 'pro_img', 'check_in_points','type_of_traveler', 'subscription_verified')->limit('6')->get();
        $rank = 1;
        $leaderbord->each(function ($user) use (&$rank) {
            // dd($user->check_in_points); 
            $user->rank = $rank;
            if($user->pro_img){
                $user->img_url = asset('public/admin-assets/img/users/' . $user->pro_img);
            }else{
                $user->img_url = null;
            }
            $user->localevel = $this->calculateStage($user->check_in_points);
            $rank++;
        }); 

            $remaining = " ";
            $users = UserModel::where('id', $auth)->select('is_private', 'full_name', 'username', 'subscription_verified', 'email', 'bio', 'gender', 'dob', 'pro_img', 'phone', 'instagram_links', 'facebook_links', 'tik_tok', 'linked_id', 'discord', 'check_in_points','type_of_traveler')->orderBy('id', 'desc')->first();
            if ($users->check_in_points >= 1 && $users->check_in_points <= 100) { 
                $stage = 1;
                $remaining = $users->check_in_points - 1108.16;
            } elseif ($users->check_in_points >= 101.16 && $users->check_in_points <= 1108.16) {
                $stage = 2;
                $remaining = $users->check_in_points - 2116.32;
            } elseif ($users->check_in_points >= 1019.16 && $users->check_in_points <= 2116.32) {
                $stage = 3;
                $remaining = $users->check_in_points - 3124.48;
            } elseif ($users->check_in_points >= 2117.32 && $users->check_in_points <= 3124.48) {
                $stage = 4;
                $remaining = $users->check_in_points - 4132.64;
            } elseif ($users->check_in_points >= 3125.48 && $users->check_in_points <= 4132.64) {
                $stage = 5;
                $remaining = $users->check_in_points - 5140.80;
            } elseif ($users->check_in_points >= 4133.64 && $users->check_in_points <= 5140.80) {
                $stage = 6;
                $remaining = $users->check_in_points - 6148.96;
            } elseif ($users->check_in_points >= 5141.80 && $users->check_in_points <= 6148.96) {
                $stage = 7;
                $remaining = $users->check_in_points - 7157.12;
            } elseif ($users->check_in_points >= 6149.96 && $users->check_in_points <= 7157.12) {
                $stage = 8;
                $remaining = $users->check_in_points - 7157.12;
            } else {
                $stage = 0;
            }
            $remaining = abs($remaining);
            $checkFollow = Follow::where('user_id', $auth)->where('following_id', $auth)->first();
            if ($checkFollow) {
                $users['is_following'] = $checkFollow->status;
            } else {
                $users['is_following'] = null;
            }

            if($users->pro_img){
                $users['profile_url'] = asset('public/admin-assets/img/users/' . $users->pro_img);
            }else{
                $users['profile_url'] = null;
            }

            $countFollowing = Follow::where('user_id', $auth)->count();
            $countFollowers = Follow::where('following_id', $auth)->count();
            $users['followers_count'] = $countFollowers;
            $users['following_count'] = $countFollowing; 
            $users['goloca_level'] = $stage;
            if ($remaining == " ") {
                $users['Need_for_next_level'] = (int)'100';
            } else {
                $users['Need_for_next_level'] = round($remaining, 2);
            }

            $showCountryHistory = CheckIn::where('user_id', $auth)->get();
            $totalCon = "195";
            $complete = (count($showCountryHistory) * $totalCon) / 100;
            $users['world_discoverd']  =  intval($complete, '1');
            $country_visited  =  CheckIn::where('user_id', $auth)->select('country')->groupBy('country')->get();
            $users['country_visited']  =  count($country_visited);
            $users['amount_tript']     =  count($showCountryHistory);
            // dd($users->is_private);
           
            if ($users->check_in_points >= 1 && $users->check_in_points <= 100) { 
                $stage = 1;
                $remaining = $users->check_in_points - 1108.16;
            } elseif ($users->check_in_points >= 101.16 && $users->check_in_points <= 1108.16) {
                    $stage = 2;
                    $remaining = $users->check_in_points - 2116.32;
                } elseif ($users->check_in_points >= 1019.16 && $users->check_in_points <= 2116.32) {
                    $stage = 3;
                    $remaining = $users->check_in_points - 3124.48;
                } elseif ($users->check_in_points >= 2117.32 && $users->check_in_points <= 3124.48) {
                    $stage = 4;
                    $remaining = $users->check_in_points - 4132.64;
                } elseif ($users->check_in_points >= 3125.48 && $users->check_in_points <= 4132.64) {
                    $stage = 5;
                    $remaining = $users->check_in_points - 5140.80;
                } elseif ($users->check_in_points >= 4133.64 && $users->check_in_points <= 5140.80) {
                    $stage = 6;
                    $remaining = $users->check_in_points - 6148.96;
                } elseif ($users->check_in_points >= 5141.80 && $users->check_in_points <= 6148.96) {
                    $stage = 7;
                    $remaining = $users->check_in_points - 7157.12;
                } elseif ($users->check_in_points >= 6149.96 && $users->check_in_points <= 7157.12) {
                    $stage = 8;
                    $remaining = $users->check_in_points - 7157.12;
                } else {
                    $stage = 0;
                }
                $remaining = abs($remaining);
                // $users['profile_url'] = asset('public/admin-assets/img/users/' . $users->pro_img);
                if($users->pro_img){
                    $users['profile_url'] = asset('public/admin-assets/img/users/' . $users->pro_img);
                }else{
                    $users['profile_url'] = null;
                }
                $users['goloca_level'] = $stage;
                if ($remaining == " ") {
                    $users['Need_for_next_level'] = (int)'100';
                } else {
                    $users['Need_for_next_level'] = round($remaining, 2);
                }

                $showCountryHistory = CheckIn::where('user_id', $auth)->get();
                $totalCon = "195";
                $complete = (count($showCountryHistory) * $totalCon) / 100;
                $users['world_discoverd']  =  intval($complete, '1');
                $country_visited  =  CheckIn::where('user_id', $auth)->select('country')->groupBy('country')->get();
                $users['country_visited']  =  count($country_visited);
                $users['amount_tript']     =  count($showCountryHistory);

                $usersss = UserModel::orderBy('id', 'desc');
                $country = Country::orderBy('id', 'desc');
                $city = City::orderBy('id', 'desc');
                $checkIn = CheckIn::orderBy('id', 'desc');;

                $checkCountrys = $checkIn->where('user_id', $auth)->with('countryNames')->select('country')->groupBy('country')->limit('6')->get();
                foreach ($checkCountrys as $key => $checkCountry) {
                    $cd = $checkCountry->country;
                    $checkCountrys[$key]['countryNames']['countryImage'] = asset('public/admin-assets/img/country/country_badges/' . $checkCountry->countryNames->badges);
                    $checkCountrys[$key]['countryVisiting'] = CheckIn::where('country', $checkCountry->country)->count();
                    $totalConUsers = $usersss->count();
                    $visitedUsersCountrys = CheckIn::where('country', $cd)->select('country', DB::raw('count(*) as total'))->groupBy('country')->get()->toArray();
                    foreach ($visitedUsersCountrys as $userkey => $usersCountrys) {
                        $checkCountrys[$key]['totalPercentageCountryUsers'] = $totalConUsers * $usersCountrys['total'] / 100;
                    }
                }

                $checkCitys = CheckIn::where('user_id', $auth)->with('cityNames')->select('city')->groupBy('city')->limit('6')->get();
                foreach ($checkCitys as $key => $checkCity) {
                    $ct = $checkCity->city;
                    $checkCitys[$key]['cityVisiting'] = CheckIn::where('city', $checkCity['city'])->count();
                    $checkCitys[$key]['cityNames']['cityimages'] = asset('public/admin-assets/img/city/' . $checkCity['cityNames']['city_badges']);
                    $totalCityUsers = $usersss->count();
                    $visitedUsersCitys = CheckIn::where('city', $ct)->select('city', DB::raw('count(*) as total'))->groupBy('city')->get()->toArray();
                    foreach ($visitedUsersCitys as $visitkey => $usersCitysValue) {
                        $checkCitys[$key]['totalPercentageCityUsers'] = ($totalCityUsers * $usersCitysValue['total']) / 100;
                    }
                }

                $myfeed = array();
                $myfeeds = Feed::where('user_id', $auth)->select('image')->orderBy('id', 'desc')->get()->makeHidden('feeds_url',)->makeHidden('image');
                foreach ($myfeeds as $key => $feeds) {
                    $decodes = json_decode($feeds->image, true);
                    foreach ($decodes as $keyimage => $value) {
                        $myfeed[] = $value;
                    }
                }
                $myfeedss = array();
                foreach ($myfeed as $keys => $values) {
                    $extension = pathinfo($values, PATHINFO_EXTENSION);
                    if($extension == 'mp4' || $extension == 'avi' || $extension == 'MOV' || $extension == 'mpeg' || $extension == 'mkv' || $extension == 'gif') {
                        $myfeedss['videos'][] = asset('public/admin-assets/img/user_feeds/' . $values);
                    }elseif ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'bmp' || $extension == 'tif') {
                        
                        $myfeedss['images'][] = asset('public/admin-assets/img/user_feeds/' . $values);
                    }
                }

                $pastTravel = CheckIn::where('user_id', $auth)->orderBy('id', 'desc')->with('countryNames', function ($query) {
                    $query->select('id', 'country_name');
                })->with('cityNames', function ($querys) {
                    $querys->select('id', 'city_name', 'city_badges');
                })->limit('6')->get();
                foreach ($pastTravel as $keysss => $past) {
                    $pastTravel[$keysss]['cityNames']['city_url'] = asset('public/admin-assets/img/city/' . $past['cityNames']['city_badges']);
                    //======================= geting between days start or end date =========================
                    $formatted_dt1 = Carbon::parse($past->start_date);
                    $formatted_dt2 = Carbon::parse($past->end_date);
                    $date_diff = $formatted_dt1->diffInDays($formatted_dt2);
                    $pastTravel[$keysss]['days'] = $date_diff;
                    $data[] = $past->country;
                    //======================= past travel users data =========================================
                    $amountOfUsers = CheckIn::where('user_id', '!=', $auth)->where('city', $past->city)->pluck('user_id')->all();
                    $getUsers = $usersss->whereIn('id', $amountOfUsers)->select('id', 'pro_img')->get();
                    foreach ($getUsers as $keyid => $getuser) {
                        $getUsers[$keyid]['profileUrls'] = asset('public/admin-assets/img/users/' . $getuser->pro_img);
                        $getUsers[$keyid]['x_more_users'] = count($getUsers);
                    }
                    $pastTravel[$keysss]['usersDetails'] = $getUsers;
                }

                return array('status' => 'Success', 'users' => $users, 'leaderbord' => $leaderbord, 'countrys' => $checkCountrys, 'citys' => $checkCitys); 
       
    }

    public function other_pro_textFeed_seeAll(Request $request){
        
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;
        
            $auth =Auth::guard('sanctum')->user()->id;
            $textfeeds = Feed::where('user_id', $request->user_id)
            ->where('is_text', 'true')
            // ->select('image')
            ->where('group_id', null)  
            ->orderBy('id', 'desc') 
            ->take($per_page_limit)
            ->skip($offset)
            ->get()
            ->makeHidden(['feeds_url', 'image']);
            if($textfeeds->toArray()){
       
                $textfeeds->transform(function($textfeeds){
                    $user_id = $textfeeds->user_id;
                    $user_id =  collect($user_id)->map(function($item) use($textfeeds){
                        
                        $user = UserModel::find($item);
                        $userId = $user['id'];
                        $textfeeds['is_private'] = $user['is_private'];
                        $status = $textfeeds['is_private'];
                        $textfeeds = $textfeeds->toArray();  
                            array_push($textfeeds,$status); 
                             return $textfeeds;  
                    })->all();
                    return $textfeeds;
                });
                    // dd($textfeeds[0]['user_id']);
                foreach($textfeeds as $key => $item){ 
                    $follower = Follow::where('following_id',Auth::guard('sanctum')->user()->id)->pluck('user_id')->toArray(); 
                    $inArray = array_search(Auth::guard('sanctum')->user()->id,$follower); 
                    $UserLike = UserLike::where('feed_id',$item['id'])->where('like_unlike','=','1')->get()->toArray();
                    $is_Like = UserLike::where('user_id',Auth::guard('sanctum')->user()->id)->where('liked_user_id','=', $item['user_id'])->where('feed_id','=', $item['id'])->where('like_unlike','=','1')->get()->toArray();
        
                    if($is_Like){
                        $is_Like = 'true';
                        $is_Like = filter_var($is_Like, FILTER_VALIDATE_BOOLEAN);
                    }else{
                        $is_Like = 'false';
                        $is_Like = filter_var($is_Like, FILTER_VALIDATE_BOOLEAN);
                    }
                    $textfeeds[$key]['is_like'] = $is_Like;
                    $UserComment = Comment::where('feed_id',$item['id'])->get()->toArray(); 
                    $users = UserModel::where('id', $item['user_id'])->first()->toArray();
                    // dd($users);
                    $today = Carbon::today()->toDateString();
                    
                    $check_in = CheckIn::where('user_id',$item['user_id'])->orderBy('id', 'desc')->first();
                    if($check_in){
                        if ($check_in && ($check_in['start_date'] < $today || $check_in['end_date'] > $today)) {
                            $textfeeds[$key]['checkedIn_country'] = $check_in['country'];
                            $textfeeds[$key]['checkedIn_city'] = $check_in['city'];
                            // dd($check_in,$item['user_id']);
                 }else{
                    
                    $textfeeds[$key]['checkedIn_country'] = 'not checkedIn';
                    $textfeeds[$key]['checkedIn_city'] = 'not checkedIn';
                 }
                }else{
                    $textfeeds[$key]['checkedIn_country'] = 'not checkedIn';
                    $textfeeds[$key]['checkedIn_city'] = 'not checkedIn';
                }
                   
                    
                    $date = Carbon::parse($item['created_at']); 
                    $formattedDate = $date->format('j M, Y');  
                    $textfeeds[$key]['date'] =  $formattedDate;
                    $textfeeds[$key]['user_details'] =  $users;
                     
                    if($item['is_private'] == '1'){  
                        if(!$inArray){
                            // unset($textfeeds[$key]); 
                        } 
                    }   
        
                    if($UserLike){  
                        $textfeeds[$key]['like'] = (integer)count($UserLike); 
                        }else{ 
                        $textfeeds[$key]['like'] = (integer)'0';
                    } 
        
                    if($UserComment){  
                        $textfeeds[$key]['Comment'] = (integer)count($UserComment); 
                        }else{ 
                        $textfeeds[$key]['Comment'] = (integer)'0';
                    }  
                } 
            
            }else{
                
                $textfeeds = [];
            }
       


        return array('status' => 'Success','testFeeds'=>$textfeeds);
    }

    public function my_pro_textFeed_seeAll(Request $request){
        
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;
        
            $auth =Auth::guard('sanctum')->user()->id;
            $textfeeds = Feed::where('user_id', $auth)
            ->where('is_text', 'true')
            // ->select('image')
            ->where('group_id', null)  
            ->orderBy('id', 'desc') 
            ->take($per_page_limit)
            ->skip($offset)
            ->get()
            ->makeHidden(['feeds_url', 'image']);
            if($textfeeds->toArray()){
       
                $textfeeds->transform(function($textfeeds){
                    $user_id = $textfeeds->user_id;
                    $user_id =  collect($user_id)->map(function($item) use($textfeeds){
                        
                        $user = UserModel::find($item);
                        $userId = $user['id'];
                        $textfeeds['is_private'] = $user['is_private'];
                        $status = $textfeeds['is_private'];
                        $textfeeds = $textfeeds->toArray();  
                            array_push($textfeeds,$status); 
                             return $textfeeds;  
                    })->all();
                    return $textfeeds;
                });
                    // dd($textfeeds[0]['user_id']);
                foreach($textfeeds as $key => $item){ 
                    $follower = Follow::where('following_id',Auth::guard('sanctum')->user()->id)->pluck('user_id')->toArray(); 
                    $inArray = array_search(Auth::guard('sanctum')->user()->id,$follower); 
                    $UserLike = UserLike::where('feed_id',$item['id'])->where('like_unlike','=','1')->get()->toArray();
                    $is_Like = UserLike::where('user_id',Auth::guard('sanctum')->user()->id)->where('liked_user_id','=', $item['user_id'])->where('feed_id','=', $item['id'])->where('like_unlike','=','1')->get()->toArray();
        
                    if($is_Like){
                        $is_Like = 'true';
                        $is_Like = filter_var($is_Like, FILTER_VALIDATE_BOOLEAN);
                    }else{
                        $is_Like = 'false';
                        $is_Like = filter_var($is_Like, FILTER_VALIDATE_BOOLEAN);
                    }
                    $textfeeds[$key]['is_like'] = $is_Like;
                    $UserComment = Comment::where('feed_id',$item['id'])->get()->toArray(); 
                    $users = UserModel::where('id', $item['user_id'])->first()->toArray();
                    // dd($users);
                    $today = Carbon::today()->toDateString();
                    
                    $check_in = CheckIn::where('user_id',$item['user_id'])->orderBy('id', 'desc')->first();
                    if($check_in){
                        if ($check_in && ($check_in['start_date'] < $today || $check_in['end_date'] > $today)) {
                            $textfeeds[$key]['checkedIn_country'] = $check_in['country'];
                            $textfeeds[$key]['checkedIn_city'] = $check_in['city'];
                            // dd($check_in,$item['user_id']);
                 }else{
                    
                    $textfeeds[$key]['checkedIn_country'] = 'not checkedIn';
                    $textfeeds[$key]['checkedIn_city'] = 'not checkedIn';
                 }
                }else{
                    $textfeeds[$key]['checkedIn_country'] = 'not checkedIn';
                    $textfeeds[$key]['checkedIn_city'] = 'not checkedIn';
                }
                   
                    
                    $date = Carbon::parse($item['created_at']); 
                    $formattedDate = $date->format('j M, Y');  
                    $textfeeds[$key]['date'] =  $formattedDate;
                    $textfeeds[$key]['user_details'] =  $users;
                     
                    if($item['is_private'] == '1'){  
                        if(!$inArray){
                            // unset($textfeeds[$key]); 
                        } 
                    }   
        
                    if($UserLike){  
                        $textfeeds[$key]['like'] = (integer)count($UserLike); 
                        }else{ 
                        $textfeeds[$key]['like'] = (integer)'0';
                    } 
        
                    if($UserComment){  
                        $textfeeds[$key]['Comment'] = (integer)count($UserComment); 
                        }else{ 
                        $textfeeds[$key]['Comment'] = (integer)'0';
                    }  
                } 
            
            }else{
                
                $textfeeds = [];
            }
       


        return array('status' => 'Success','testFeeds'=>$textfeeds);
    }



}
