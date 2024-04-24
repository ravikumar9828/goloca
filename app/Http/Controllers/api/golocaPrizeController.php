<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\UserModel;
use App\Models\ExpLevel;
use App\Models\Country;
use App\Models\Comment;
use App\Models\CommentResponse;
use App\Models\CheckInLocation;
use App\Models\City;
use App\Models\Follow;
use App\Models\UserLike;
use App\Models\goloca_price;
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

class golocaPrizeController extends Controller
{
    private function calculateStage($checkInPoints)
    {
        $stages = [
            0, 100, 1108.16, 2116.32, 3124.48, 4132.64, 5140.80, 6148.96, 7157.12, 8165.28, 9173.44, 10181.60, 11189.76, 12197.92, 13206.08, 14214.24, 15222.40, 16230.56, 17238.72, 18246.88, 19255.04, 20263.20, 21271.36, 22279.52, 23287.68, 24295.84, 25304.00, 26312.16, 27320.32, 28328.48, 29336.64, 30344.80, 31352.96, 32361.12, 33369.28, 34377.44, 35385.60, 36393.76, 37401.92, 38410.08, 39418.24, 40426.40, 41434.56, 42442.72, 43450.88, 44459.04, 45467.20, 46475.36, 47483.52, 48491.68, 49499.84, 50508.00, 51516.16, 52524.32, 53532.48, 54540.64, 55548.80, 56556.96, 57565.12, 58573.28, 59581.44, 60589.60, 61597.76, 62605.92, 63614.08, 64622.24, 65630.40, 66638.56, 67646.72, 68654.88, 69663.04, 70671.20, 71679.36, 72687.52, 73695.68, 74703.84, 75712.00, 76, 720.16, 77, 728.32, 78, 736.48, 79744.64, 80752.80, 81760.96, 82769.12, 83777.28, 84785.44, 85793.60, 86801.76, 87809.92, 88818.08, 89826.24, 90834.40, 91842.56, 92850.72, 93858.88, 94867.04, 95875.20, 96883.36, 97891.52, 98899.68
        ];

        foreach ($stages as $index => $threshold) {
            if ($checkInPoints < $threshold) {
                return $index;
            }
        } 
        return count($stages) - 1;
    }

    public function golocaPrizes(Request $request){
        
        $per_page_limit = $request->per_page;
        $page_no = $request->page_no;
        $offset = ($page_no - 1) * $per_page_limit; 
        $currentDate = Carbon::now()->toDateString();
        $goloca_price = goloca_price::where(function ($query) use ($currentDate) {
                $query->where('start_date', '<=', $currentDate) 
                    //   ->Where('end_date', '>=', $currentDate); 
                    ->where(function ($query) use ($currentDate) {
                        $query->where('end_date', '>=', $currentDate)
                              ->orWhereNull('end_date');
                  });
                      
            })
            ->Where('status', '=', 'active')
            ->get()->toArray();
            foreach($goloca_price as $key => $val){
                $is_participate = false; 
                $goloca_price[$key]['participants_userId'] = json_decode($goloca_price[$key]['participants_userId'],true); 
               $is_participate_user = $goloca_price[$key]['participants_userId']; 
               if($is_participate_user){
                $arr_search = array_search(auth('sanctum')->user()->id,$is_participate_user);
                if($arr_search !== false){
                    $is_participate = true;
                } 
            }
            $goloca_price[$key]['is_participate'] = $is_participate;
                
            } 
        $goloca_price = array_slice($goloca_price, $offset, $per_page_limit);
        return response()-> json(['status'=>'success','data'=>$goloca_price]);
        
    }
    public function selectedGolocaPrizes(Request $request){
        $goloca_price  =  goloca_price::find($request->id);
        $participants_userId = json_decode($goloca_price->participants_userId,true); 
        $is_participate = false;
        // dd($participants_userId);
        if($participants_userId){
            $arr_search = array_search(auth('sanctum')->user()->id,$participants_userId);
            if($arr_search !== false){
                $is_participate = true;
            } 
        } 
        $goloca_price =  $goloca_price->toArray();
        $goloca_price['participants_userId'] = json_decode($goloca_price['participants_userId'],true);
        $goloca_price['is_participate'] = $is_participate;
        if(!$goloca_price){
            return response()-> json(['status'=>'failed','data'=>'something went wrong']);
        }
        return response()-> json(['status'=>'success','data'=>$goloca_price]);
        
    }

    public function participateGolocaPrizes(Request $request){
        $user_id = auth('sanctum')->user()->id;
        $user = UserModel::find($user_id);
        $goloca_lavel = $this->calculateStage($user->check_in_points);
        $goloca_price  =  goloca_price::find($request->id);
        if(!$goloca_price){
            return response()-> json(['status'=>'failed','message'=>'not found']);
        }
        if($goloca_lavel >= $goloca_price->min_goloca_level){
            if($goloca_price->total_participants  < $goloca_price->min_participants ){
            $autharr = [(string)auth('sanctum')->User()->id];
            $goloca_price->total_participants = ($goloca_price->total_participants)+1;
            if($goloca_price->participants_userId == null){ 
                $goloca_price->participants_userId = json_encode($autharr, JSON_FORCE_OBJECT);
            }else{
                $participants_userId = json_decode($goloca_price->participants_userId,true); 
                $arr_search = array_search(auth('sanctum')->user()->id,$participants_userId);  
                if($arr_search !== false){
                    return response()-> json(['status'=>'success','message'=>'already participate']);
                }else{
                    array_push($participants_userId,(string)auth('sanctum')->User()->id); 
                    $goloca_price->participants_userId = json_encode($participants_userId, JSON_FORCE_OBJECT);
                    $goloca_price->save();
                    return response()-> json(['status'=>'success','message'=>'participate successful']); 
                }
            }
            $goloca_price->save();
            return response()-> json(['status'=>'success','message'=>'participate successful']);
        }else{
            return response()-> json(['status'=>'failed','message'=>'min. participate limit is full']);
        }
        
        }else{
            return response()-> json(['status'=>'failed','message'=>'min. goloca label required is '.$goloca_price->min_goloca_level]);
        } 

    }

    public function cancelGolocaPrizes(Request $request){ 
        $user_id = auth('sanctum')->user()->id; 
        $goloca_price  =  goloca_price::find($request->id);
        if(!$goloca_price){
            return response()-> json(['status'=>'failed','message'=>'not found']);
        }
       $participants_userId = json_decode($goloca_price->participants_userId,true);
   
       if($participants_userId != null){
       $arr_search = array_search($user_id,$participants_userId);
       if($arr_search !== false){
            unset($participants_userId[$arr_search]);
           $array_set = array_values($participants_userId);
           $goloca_price->participants_userId = json_encode($array_set,JSON_FORCE_OBJECT);
           $goloca_price->total_participants = ($goloca_price->total_participants)-1;
           $goloca_price->save();
           return response()-> json(['status'=>'success','message'=>'canceled successful']);
       }else{
        return response()-> json(['status'=>'failed','message'=>'not found']);
       }
    }else{
        return response()-> json(['status'=>'failed','message'=>'participants not found']); 
    }   
        
    }














   
}




