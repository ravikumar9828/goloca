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
use App\Models\Feed;
use App\Models\FcmToken;
use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDO;
use Auth;
use Session;

class testController extends Controller
{
    public function user_home_post(Request $request){
       
        $userId = Auth::guard('sanctum')->user()->id;  
       $users_followers = Follow::where('user_id', $userId)->pluck('following_id')->toArray(); 
       $releavent = trim($request->releavent);
       $start_date = trim($request->start_date);
       $end_date = trim($request->end_date);
       
       $per_page_limit = $request->per_page;           
       $page_no = $request->page_no;                   
       $offset = ($page_no - 1) * $per_page_limit; 
       
    //    $latest_post = Feed::orderBy('created_at', 'DESC');
       $latest_post = Feed::whereNot('user_id', Auth::guard('sanctum')->user()->id)->whereNull('group_id');
        if ($start_date !== 'null' && $start_date !== null) {
        $start_date = Carbon::create($start_date, 1, 1, 0, 0, 0);  
        $end_date = Carbon::create($end_date, 12, 31, 23, 59, 59);  

         $latest_post->whereBetween('created_at', [$start_date, $end_date])
         ->whereNotIn('user_id', [$userId]);
        }
        if ($releavent == 'true') {  
            $latest_post->select('feeds.*')
            ->where(function ($query) use ($userId) {
                $query->whereIn('feeds.city', function ($subQuery) use ($userId) {
                        $subQuery->select('city')
                                ->from('check_ins')
                                ->where('user_id', $userId) 
                                ->where('verified_location', 1);
                })
                ->orWhereIn('feeds.user_id', function ($subQuery) use ($userId) {
                        $subQuery->select('user_id')
                                ->from('follows')
                                ->whereIn('following_id', [$userId])
                                ->orWhereIn('user_id', [$userId]);
                });
            })
            ->whereHas('userDetails', function ($query) {
                $query->where('is_private', 0);
            }); 
           
        }
         
        // $latest_post = $latest_post->take($per_page_limit)->skip($offset)->get();
        $latest_post = $latest_post // Shuffle the results randomly
        ->orderBy('created_at', 'desc')
        ->take($per_page_limit)
        ->skip($offset) 
        ->get()->makeHidden('user_taging');
        // $latest_post = $latest_post->shuffle();
        // dd(count($latest_post));  
        $latest_post->transform(function($latest_post){
            $user_id = $latest_post->user_id;
            $user_id =  collect($user_id)->map(function($item) use($latest_post){
                
                $user = UserModel::find($item);
                $userId = $user['id'];
                $latest_post['is_private'] = $user['is_private'];
                $status = $latest_post['is_private'];
                $latest_post = $latest_post->toArray();  
                    array_push($latest_post,$status); 
                     return $latest_post;  
            })->all();
            return $latest_post;
        });
       
        foreach($latest_post as $key => $item){ 
           
            $follower = Follow::where('following_id',Auth::guard('sanctum')->user()->id)->pluck('user_id')->toArray(); 
            $inArray = array_search(Auth::guard('sanctum')->user()->id,$follower); 
            $UserLike = UserLike::where('feed_id',$item['id'])->where('like_unlike','=','1')->get()->toArray();
            $is_Like = UserLike::where('user_id',$userId)->where('liked_user_id','=', $item['user_id'])->where('feed_id','=', $item['id'])->where('like_unlike','=','1')->get()->toArray();

            if($is_Like){
                $is_Like = 'true';
                $is_Like = filter_var($is_Like, FILTER_VALIDATE_BOOLEAN);
            }else{
                $is_Like = 'false';
                $is_Like = filter_var($is_Like, FILTER_VALIDATE_BOOLEAN);
            }
            $latest_post[$key]['is_like'] = $is_Like;
            $UserComment = Comment::where('feed_id',$item['id'])->get()->toArray(); 
            $users = UserModel::where('id', $item['user_id'])->first()->toArray();
            $today = Carbon::today()->toDateString();
           
            
            $check_in = CheckIn::where('user_id',$item['user_id'])->orderBy('id', 'desc')->first();
            if($check_in){
                if ($check_in && ($check_in['start_date'] < $today || $check_in['end_date'] > $today)) {
                    $latest_post[$key]['checkedIn_country'] = $check_in['country'];
                    $latest_post[$key]['checkedIn_city'] = $check_in['city'];
                    // dd($check_in,$item['user_id']);
         }else{
            
            $latest_post[$key]['checkedIn_country'] = 'not checkedIn';
            $latest_post[$key]['checkedIn_city'] = 'not checkedIn';
         }
        }else{
            $latest_post[$key]['checkedIn_country'] = 'not checkedIn';
            $latest_post[$key]['checkedIn_city'] = 'not checkedIn';
        }
           
            
            $date = Carbon::parse($item['created_at']); 
            $formattedDate = $date->format('j M, Y');  
            $latest_post[$key]['date'] =  $formattedDate;
            $latest_post[$key]['user_details'] =  $users;
             
            if($item['is_private'] == '1'){  
                if(!$inArray){
                    // unset($latest_post[$key]); 
                } 
            }   
          
            if($UserLike){  
               
                $latest_post[$key]['like'] = (integer)count($UserLike); 
                }else{ 
                   
                $latest_post[$key]['like'] = (integer)'0';
            } 
            if($UserComment){  
                $latest_post[$key]['Comment'] = (integer)count($UserComment); 
                }else{ 
                   
                $latest_post[$key]['Comment'] = (integer)'0';
            } 
           
        } 
       

        return array('status' => 'Success', 'homePostData' => $latest_post->toArray()); 
    }


    public function socialite_login(Request $request){
    
        // dd( $request->all());
        if($request->type == 'apple' && !Empty($request->email)){  
           
            $validateUser = Validator::make(
                $request->all(),
                [
                    'type' => ['required', 'in:google,facebook,apple'],
                    'email' => 'required|email',
                    'unique_id' => 'required',
                     
                ],
                [
                    'type.required' => 'The type field is required.',
                    'type.in' => 'Invalid type',
                    'email.required' => 'The email field is required.',
                    'unique_id.required' => 'The unique_id field is required.',
                    'email.email' => 'Invalid email.',
                     
                ]
            ); 
        }else if($request->type == 'apple' && Empty($request->email)){
          
            $validateUser = Validator::make(
                $request->all(),
                [
                    'type' => ['required', 'in:google,facebook,apple'],
                    // 'email' => 'required|email',
                    'unique_id' => 'required',
                     
                ],
                [
                    'type.required' => 'The type field is required.',
                    'type.in' => 'Invalid type',
                    // 'email.required' => 'The email field is required.',
                    'unique_id.required' => 'The unique_id field is required.',
                    // 'email.email' => 'Invalid email.',
                     
                ]
            ); 
        }
        else{
        $validateUser = Validator::make(
            $request->all(),
            [
                'type' => ['required', 'in:google,facebook,apple'],
                'email' => 'required|email',
                'unique_id' => 'required',
                 
            ],
            [
                'type.required' => 'The type field is required.',
                'type.in' => 'Invalid type',
                'email.required' => 'The email field is required.',
                'unique_id.required' => 'The unique_id field is required.',
                'email.email' => 'Invalid email.',
                 
            ]
        ); 
    }
        
        if ($validateUser->fails()) {
            return response()->json(['errors' => $validateUser->errors()], 501);
        }  

 

            if($request->type == 'apple' && !Empty($request->email)){
                $user = UserModel::where('email', $request->email)->first(); 
    
            }else if($request->type == 'apple' && Empty($request->email)){
                $user = UserModel::where('apple_login', $request->unique_id)->first();  
                // dd($user,$request->unique_id,);
            }else{
                $user = UserModel::where('email', $request->email)->first();
            }
       
        if(!$user){
            // dd('ok');
                $user = new UserModel(); 
                $user->email =  $request->email;
            if($request->type == 'google'){  
                $user->google_login = $request->unique_id;  
            }else if($request->type == 'facebook'){ 
                $user->facebook_login = $request->unique_id; 
            }else if($request->type == 'apple'){ 
                $user->apple_login = $request->unique_id; 
            }  
                $user->status =  'active';
                $user->username =  $request->username;
                $user->full_name =  $request->full_name;
                $user->save();
                if($user->save()){
                    $user = UserModel::latest()->first();
                    if(!empty($request->token)){
                     $notify = new FcmToken();
                     $notify->user_id = $user->id;
                     $notify->notify_token = $request->token;
                     $notify->save();
                 }
                 }
                 return response()->json([
                    'status' => true,
                    'message' => 'successful',
                    'token' => $user->createToken("Myapp")->plainTextToken,
                    'data' => ['user_id'=>$user->id,'full_name'=>$user->full_name,'username'=>$user->username,'pro_img'=>$user->pro_img,'email'=>$user->email]
                     ], 200);  
         }  
                if($request->type == 'google'){  
                    $user->google_login = $request->unique_id;  
                }else if($request->type == 'facebook'){ 
                    $user->facebook_login = $request->unique_id; 
                }else if($request->type == 'apple'){ 
                    $user->apple_login = $request->unique_id; 
                } 
                // if(!Empty($request->username) && !Empty($user->username) ){
                //     $user->username =  $request->username;
                // } 
                // if(!Empty($request->full_name) && !Empty($user->full_name)){
                //     $user->full_name =  $request->full_name;
                // }  
                
                $user->save();
              
                if($user->save()){ 
                   if(!empty($request->token)){
                    $notify = new FcmToken();
                    $notify->user_id = $user->id;
                    $notify->notify_token = $request->token;
                    $notify->save();

                }
            }


            return response()->json([
            'status' => true,
            'message' => 'successful',
            'token' => $user->createToken("Myapp")->plainTextToken,
            'data' => ['user_id'=>$user->id,'full_name'=>$user->full_name,'username'=>$user->username,'pro_img'=>$user->pro_img,'email'=>$user->email]
             ], 200); 
        

    }



















   
}




