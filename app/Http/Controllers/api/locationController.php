<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\UserModel;
use App\Models\ExpLevel;
use App\Models\Country;
use App\Models\Comment;
use App\Models\CommentResponse;
use App\Models\Group;
use App\Models\CheckInLocation;
use App\Models\City;
use App\Models\Follow;
use App\Models\UserLike;
use App\Models\Feed;
use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDO;
use Auth;
use Session;

class locationController extends Controller
{
    public function select_user_location_api(Request $request){
        // $request->id = 38;
        $city = City::where('id', $request->id)->with(['countryName:id,country_name'])->get()->toArray();
        $CheckInCount = CheckIn::where('city',$request->id)->where('verified_location','=',1)->get()->toArray(); 
        $city[0]['checked_in'] = count($CheckInCount);

        $Groups = Group::where('group_city',$request->id)->get()->toArray();
        $CheckInUser_id = CheckIn::where('city',$request->id)->where('verified_location','=',1)->pluck('user_id')->toArray();
        // dd($CheckInUser_id);
        $User = UserModel::whereIn('id',$CheckInUser_id)->get();
        
       return array('status' => 'Success', 'city_and_country' => $city, 'Groups' => $Groups,'checked_in' => $User);

    }
}






























    // foreach($Groups as $key => $item){ 
    //     if($item['group_status'] == '1'){
    //         $follower = Follow::where('following_id',$item['creator_id'])->pluck('user_id')->toArray(); 
    //         array_push($follower,Auth::guard('sanctum')->user()->id);
    //         $inArray = array_search(Auth::guard('sanctum')->user()->id,$follower);
    //         if(!$inArray){
    //         // dd($follower);
    //         unset($Groups[$key]);
    //     }
    // }
    // } 