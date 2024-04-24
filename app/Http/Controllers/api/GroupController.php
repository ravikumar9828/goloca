<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\CheckIn;
use App\Models\UserModel;
use App\Models\Follow; 
use App\Models\Feed; 
use App\Models\CheckInLocation;
use App\Models\UserLike;
use App\Models\Comment;
use Carbon\Carbon; 
use App\Traits\notify_trait;
use DB;
use Auth;
use Illuminate\Support\Facades\Validator;

 class GroupController extends Controller
{ 
   

    public function addMembers(Request $request)
    {
        $per_page_limit = $request->per_page;
        $page_no = $request->page_no;
        $offset = ($page_no - 1) * $per_page_limit; 
        $name = $request->name; 
        $auth = auth('sanctum')->user()->id; 

        $hide_users = 0;

        if($request->group_id != "null"){
            $group = Group::find($request->group_id);
            if($group){  
                $groups_member_json = $group['members'];
                $groups_moderators_json = $group['moderators']; 
                $groups_admins_json = $group['admins']; 
                if($groups_moderators_json){
                    $groups_moderators = json_decode($groups_moderators_json,true);
                }else{
                    $groups_moderators = [];
                }
                if($groups_member_json){
                    $groups_member = json_decode($groups_member_json,true); 
                }else{
                    $groups_member = [];
                }
                if($groups_admins_json){
                    $groups_admins = json_decode($groups_admins_json,true);
                }else{
                    $groups_member = [];
                }
            $hide_users_merge = array_merge($groups_member,$groups_moderators,$groups_admins);

            if(count($hide_users_merge) > 0){
                $hide_users = 1;
            }
        }
        
        } 

        if($hide_users == 1){ 
            $users = UserModel::whereNotIn('id', $hide_users_merge)
            ->orderBy('id', 'desc')
            ->select('id', 'is_private', 'full_name', 'username', 'pro_img', 'country', 'city', 'check_in_points', 'subscription_verified', 'type_of_traveler')
            ->when($name != 'null', function ($query) use ($name) {
                return $query->where(function ($query) use ($name) {
                    $query->where('full_name', 'like', '%' . $name . '%')
                        ->orWhere('username', 'like', '%' . $name . '%');
                });
            })
            ->where(function ($query) use ($auth) {
                $query->where('is_private', 0)  
                    ->orWhereExists(function ($subQuery) use ($auth) {
                        $subQuery->select(DB::raw(1))
                            ->from('follows')
                            ->whereRaw('follows.user_id = ? AND follows.following_id = user_models.id', [$auth]);
                    });
            })
            ->take($per_page_limit)
            ->skip($offset)
            ->get();
        }else{
            $users = UserModel::where('id', '>', 1)
                ->orderBy('id', 'desc')
                ->select('id', 'is_private', 'full_name', 'username', 'pro_img', 'country', 'city', 'check_in_points', 'subscription_verified', 'type_of_traveler')
                ->when($name != 'null', function ($query) use ($name) {
                    return $query->where(function ($query) use ($name) {
                        $query->where('full_name', 'like', '%' . $name . '%')
                            ->orWhere('username', 'like', '%' . $name . '%');
                    });
                })
                ->where(function ($query) use ($auth) {
                    $query->where('is_private', 0)  
                        ->orWhereExists(function ($subQuery) use ($auth) {
                            $subQuery->select(DB::raw(1))
                                ->from('follows')
                                ->whereRaw('follows.user_id = ? AND follows.following_id = user_models.id', [$auth]);
                        });
                })
                ->take($per_page_limit)
                ->skip($offset)
                ->get();
        }

        
        foreach ($users as $key => $user) {  
           if($user->subscription_verified = 'false'){
            $user->subscription_verified = false;
           }
           if($user->subscription_verified = 'true'){
            $user->subscription_verified = true;
           } 
            $location = CheckIn::where('user_id', $user->id)->where(['is_checkout_trip' => 0, 'verified_location' => 1])->first();
            if ($location != null) {
                $updatedLocation = CheckInLocation::where('user_id', $user->id)->where(['check_country' => $location->country, 'check_city' => $location->city])->first();
                if ($updatedLocation) {
                    $user->country = $updatedLocation->check_country;
                    $user->city    = $updatedLocation->check_city;
                } else {
                    $user->country = "Not checkedIn";
                    $user->city    = "Not checkedIn";
                }
            } else {
                $user->country = "Not checkedIn";
                $user->city    = "Not checkedIn";
            }
        }
        // dd($users);
        return response()->json(['status' => 'Success', 'users' => $users]);
    }

    public function createGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'        => 'required',
            'description'  => 'required',
            'group_country' => 'required',
            'group_city'    => 'required',
            'group_status'  => 'required',
            // 'members'  => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }
       
        $autharr = [(string)auth('sanctum')->User()->id];
        // dd($memberssss,$autharr);
        $auth = auth('sanctum')->User()->id;
        $group = new Group();
        $group->creator_id    = $auth;
        $group->title         = $request->title;
        $group->description   = $request->description;
        $group->group_country = $request->group_country;
        $group->group_city    = $request->group_city;
        $group->group_status  = $request->group_status;
        if($request->members != 'null'){
            $memberssss = explode(',', $request->members);
            $group->members       = json_encode($memberssss, JSON_FORCE_OBJECT);
        }
        $group->admins       = json_encode($autharr, JSON_FORCE_OBJECT);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/group/', $filename);
            $group->image = $filename;
        }
        $group->save();
        return response()->json(['status' => 'success', 'message' => 'Group Created successfully']);
    }

    public function perticulerGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id'  => 'required|exists:groups,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $group = Group::where('id', $request->group_id)->first();
        if (!$group) {
            return response()->json(['status' => 'failed', 'message' => 'Group not found']);
        }

        $group->image = asset('public/admin-assets/img/group/' . $group->image);
        if($group->members != null){
            $members = json_decode($group->members, true);
            $transformedMembers = [];
            $checkInLocation = [];
            foreach ($members as $key => $memberId) {
                $user = UserModel::select('id', 'is_private', 'full_name', 'username', 'pro_img', 'country', 'city', 'check_in_points')->find($memberId);
                if ($user) {
                    $user->pro_img = $user->pro_img ? asset('public/admin-assets/img/users/' . $user->pro_img) : null;
                    $transformedMembers[$key] = $user;
                }
    
                $location = CheckIn::where('user_id', $memberId)->where(['is_checkout_trip' => 0, 'verified_location' => 1])->first();
                if ($location) {
                    $updatedLocation = CheckInLocation::where('user_id', $user->id)->where(['check_country' => $location->country, 'check_city' => $location->city])->first();
                    if ($updatedLocation) {
                        $user->country = $updatedLocation->check_country;
                        $user->city = $updatedLocation->check_city;
                    } else {
                        $user->country = "not checkIn";
                        $user->city = "not checkIn";
                    }
                } else {
                    // dd($user->country,$user->city);
                    $user->country = "not checkIn";
                    $user->city = "not checkIn";
                }
            }
            $group->members = $transformedMembers;
        }else{
            $group->members = [];
        }
       

        return response()->json(['status' => 'Success', 'group' => $group]);
    }

    public function editGroup(Request $request)
    {
        // moderators
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }
        $moderators  = explode(',', $request->moderators);
        $auth = auth('sanctum')->user()->id;
        $groups = Group::where('id', $request->group_id)->first();
        $groups->creator_id = $auth;
        if($request->title != 'null'){
            $groups->title = $request->title; 
        }
        if($request->description != 'null'){
            $groups->description = $request->description; 
        }
        if($request->group_country != 'null'){
            $groups->group_country = $request->group_country;
        }
        if($request->group_city != 'null'){
            $groups->group_city = $request->group_city;
        }
        if($request->group_status != 'null'){
            if($request->group_status == '0'){
                $requested_user  = json_decode($groups->requested_user,true);
                if(!$requested_user){ 
                    $groups->group_status = $request->group_status;

                }else{
                    $members  = json_decode($groups->members,true);
                    if(!$members){
                        $groups->members =  $groups->requested_user;
                        $groups->group_status = $request->group_status;
                        $groups->requested_user = null;
                    }else{
                        $arr_mer = array_merge($members,$requested_user);
                        $data = array_values($arr_mer);
                        $groups->members = json_encode($data, JSON_FORCE_OBJECT);
                        $groups->group_status = $request->group_status;
                        $groups->requested_user = null;
                    }
                    
                }
              

            }elseif($request->group_status == '1'){
                $groups->group_status = $request->group_status;
            }
            
        }
        
        
        if($request->moderators != 'null'){ 
            $checkadmins = json_decode($groups->admins, true);
            if(!in_array($auth,$checkadmins)){
                return response()->json(['status' => 'failed', 'message' => 'you are not permited']);
            } 
            if($request->moderators == 'removed'){
                $groups->moderators = null;
    
            }else{ 
            $groups->moderators = json_encode($moderators, JSON_FORCE_OBJECT); 
        }   
        }
       

        if($request->subadmin != 'null'){ 
            // dd($request->subadmin);
            if($groups->creator_id !=  $auth){
                return response()->json(['status' => 'failed', 'message' => 'you are not permited']);
            } 
            if($request->subadmin == 'removed'){
                $autharr = [(string)auth('sanctum')->User()->id];
                $groups->admins = json_encode($autharr, JSON_FORCE_OBJECT);
            }else{
           $str = (string)auth('sanctum')->User()->id.','.$request->subadmin;
           $subadmin = explode(',', $str);
           $groups->admins = json_encode($subadmin, JSON_FORCE_OBJECT); 
            }
        }
        
        $removeMember  = explode(',', $request->removeMember);
        $addMember     = explode(',', $request->addMember); 
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/group/', $filename);
            $groups->image = $filename;
        }
        $checkMembers = json_decode($groups->members, true) ?? [];
        $commonMembers = array_intersect($checkMembers, $removeMember);
        
        if (!empty($commonMembers)) {
            $keys = array_keys($commonMembers);
            foreach ($keys as $key) {
                unset($checkMembers[$key]);
            }
        }
        $updatedMembers = array_values($checkMembers);
        if($addMember[0] != 'null'){
            // dd($addMember[0]);
            $finalMembers = array_merge($updatedMembers, $addMember);
            $groups->members = json_encode($finalMembers, JSON_FORCE_OBJECT);
        }
        
        else{ 
            
            if(!$updatedMembers){
                $groups->members = null;
            }else{
            $groups->members = json_encode($updatedMembers, JSON_FORCE_OBJECT);
            }
        }
        $groups->save();
        return response()->json(['status' => 'Success', 'message' => 'Group update successfully']);
    }

    public function exploreGroup(Request $request){ 
        // dd(auth('sanctum')->user()->id);
        $routeName = $request->route()->getName(); 

        $validator = Validator::make($request->all(), [
            'group_id'  => 'required|exists:groups,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $group = Group::where('id', $request->group_id)->first();  
        if (!$group) {
            return response()->json(['status' => 'failed', 'message' => 'Group not found']);
        }  
        $members = json_decode($group->members, true);
        $transformedMembers = [];  
        $admins = json_decode($group->admins, true);
        $admins_count = count($admins);
        $moderators = json_decode($group->moderators, true);
        $moderators_count = 0;   
        
        $transformedadmins = [];
        $transformedmoderators = [];  
        $role = 'user'; 
        $requested_user = json_decode($group->requested_user, true);

        $is_join = false;
        $is_request = false;
       
    //    dd(auth('sanctum')->user()->id);


    if($requested_user){
        if(in_array(auth('sanctum')->user()->id, $requested_user)){
            $is_request = true;
        }  
    }

        if($moderators){
            $moderators_count = count($moderators);
            if(in_array(auth('sanctum')->user()->id, $moderators)){
                $role = 'moderator';
                $is_join = true;
              
               
            } 
            foreach ($moderators as $key => $moderatorsId) {
                $user = UserModel::select('id', 'is_private', 'full_name', 'username', 'pro_img', 'check_in_points')->find($moderatorsId);
                if ($user) {
                   
                    $transformedmoderators[$key] = $user;
                }
                $follow = Follow::where('user_id',auth('sanctum')->user()->id)->where('following_id',$moderatorsId)->get()->toArray();
                if(!$follow){
                 $user->is_follow = false;
                }else{
                 $user->is_follow = true;
                } 
                 $location = CheckIn::where('user_id', $moderatorsId)->where(['is_checkout_trip' => 0, 'verified_location' => 1])->first();
                 if ($location) {
                     $updatedLocation = CheckInLocation::where('user_id', $user->id)->where(['check_country' => $location->country, 'check_city' => $location->city])->first();
                     if ($updatedLocation) {
                         $user->country = $updatedLocation->check_country;
                         $user->city = $updatedLocation->check_city;
                     } else {
                         $user->country = "not checkIn";
                         $user->city = "not checkIn";
                     }
                 } else {
                     
                     $user->country = "not checkIn";
                     $user->city = "not checkIn";
                 }
            }
        }
        if($admins){
          
            if(count($admins) == 2){
                if(auth('sanctum')->user()->id == $admins[0]){
                    $role = 'creater_admin';
                    $is_join = true;
                }
                if(auth('sanctum')->user()->id == $admins[1]){
                    $role = 'sub_admin';
                    $is_join = true;
                }
            }
            else{
                if(in_array(auth('sanctum')->user()->id, $admins)){
                    $role = 'creater_admin';
                    $is_join = true;
                } 
            }
            
           
            foreach ($admins as $key => $adminsId) {
                $user = UserModel::select('id', 'is_private', 'full_name', 'username', 'pro_img', 'check_in_points')->find($adminsId);
                if ($user) {
                   
                    $transformedadmins[$key] = $user;
                }
                $follow = Follow::where('user_id',auth('sanctum')->user()->id)->where('following_id',$adminsId)->get()->toArray();
                if(!$follow){
                 $user->is_follow = false;
                }else{
                 $user->is_follow = true;
                } 
                 $location = CheckIn::where('user_id', $adminsId)->where(['is_checkout_trip' => 0, 'verified_location' => 1])->first();
                 if ($location) {
                     $updatedLocation = CheckInLocation::where('user_id', $user->id)->where(['check_country' => $location->country, 'check_city' => $location->city])->first();
                     if ($updatedLocation) {
                         $user->country = $updatedLocation->check_country;
                         $user->city = $updatedLocation->check_city;
                     } else {
                         $user->country = "not checkIn";
                         $user->city = "not checkIn";
                     }
                 } else {
                     
                     $user->country = "not checkIn";
                     $user->city = "not checkIn";
                 }
            } 
        }

        if($members){
            if(in_array(auth('sanctum')->user()->id, $members)){
                $is_join = true;
                $role = 'member';
            }
        foreach ($members as $key => $memberId) {
            $user = UserModel::select('id', 'is_private', 'full_name', 'username', 'pro_img', 'check_in_points','type_of_traveler')->find($memberId);
            if ($user) { 
                $transformedMembers[$key] = $user;
            }
           
           $follow = Follow::where('user_id',auth('sanctum')->user()->id)->where('following_id',$memberId)->get()->toArray();
           if(!$follow){
            $user->is_follow = false;
           }else{
            $user->is_follow = true;
           } 
            $location = CheckIn::where('user_id', $memberId)->where(['is_checkout_trip' => 0, 'verified_location' => 1])->first();
            if ($location) {
                $updatedLocation = CheckInLocation::where('user_id', $user->id)->where(['check_country' => $location->country, 'check_city' => $location->city])->first();
                if ($updatedLocation) {
                    $user->country = $updatedLocation->check_country;
                    $user->city = $updatedLocation->check_city;
                } else {
                    $user->country = "not checkIn";
                    $user->city = "not checkIn";
                }
            } else {
                
                $user->country = "not checkIn";
                $user->city = "not checkIn";
            }
        }
    }
            $group->is_join = $is_join;
            $group->role = $role;
            $group->is_request = $is_request;
            $group->admins = $transformedadmins;
            $group->moderators = $transformedmoderators;
            $group->members_count = (count((array) $members) + $admins_count + $moderators_count); 


        if ($routeName === 'exploreGroupMember') {  

            $group_exp =  Group::where('id',$request->group_id)->first()->toArray();
            if($group_exp['group_status'] == 1){ 
                $member_exp =  json_decode($group_exp['members'],true); 
                if(!$member_exp){
                return array('status' => 'Success', 'groups_members' => []);
                }
            if(!in_array(auth('sanctum')->user()->id, $member_exp)){
             return array('status' => 'Success', 'groups_members' => []);
             } 
         }  
         $per_page_limit = $request->per_page;
         $page_no = $request->page_no;
         $offset = ($page_no - 1) * $per_page_limit;
         $group->members = $transformedMembers; 
         $paginatedUsers = array_slice($group['members'], $offset, $per_page_limit); 
            return response()->json(['status' => 'Success', 'groups_members' => $paginatedUsers]);
        }

         
            $per_page_limit = $request->per_page;
            $page_no = $request->page_no;
            $offset = ($page_no - 1) * $per_page_limit;
            // $group->members = $transformedMembers; 
            $paginatedUsers = array_slice($transformedMembers, $offset, $per_page_limit);  
            $group->members_list = $paginatedUsers;
            
            if($page_no == 1){ 
              
                return response()->json(['status' => 'Success', 'group' => $group->makeHidden('members')]);
            }else{
                // dd('ok');
                return response()->json(['status' => 'Success', 'group' => array('members_list'=>$group->members_list)]);
            }



    }

    
 
    public function user_group_post(Request $request){  
        // dd('ok');
        $userId = Auth::guard('sanctum')->user()->id;  
        // dd($userId);
        $releavent = trim($request->releavent);
        $start_date = trim($request->start_date);
        $end_date = trim($request->end_date); 
        $per_page_limit = $request->per_page;           
        $page_no = $request->page_no;                   
        $offset = ($page_no - 1) * $per_page_limit; 
        $group_id = $request->group_id;  
        $group_test =  Group::where('id',$group_id)->get()->toArray();
        if(!$group_test){
            return ['status' => 'Success', 'user_group_post' => []];
        } 
        $group =  Group::where('id',$group_id)->first()->toArray();
      
    //    if($group['group_status'] == 1){
    //    $member =  json_decode($group['members'],true); 
    //    if(!in_array($userId, $member)){
    //     return array('status' => 'Success', 'user_group_post' => []);
    //     } 
    // }
    if ($group['group_status'] == 1) {
        $member = json_decode($group['members'], true) ?? [];
        $admins = json_decode($group['admins'], true) ?? [];
        $moderators = json_decode($group['moderators'], true) ?? [];
        
        
        if (!in_array($userId, $member) && !in_array($userId, $admins) && !in_array($userId, $moderators)) {
            return ['status' => 'Success', 'user_group_post' => []];
        }
    }
    
    $latest_post = Feed::where('group_id', $request->group_id);
   
        if ($start_date !== 'null' && $start_date !== 'null') {
        $start_date = Carbon::create($start_date, 1, 1, 0, 0, 0);  
        $end_date = Carbon::create($end_date, 12, 31, 23, 59, 59); 
         $latest_post->whereBetween('created_at', [$start_date, $end_date]);
        //  ->whereNotIn('user_id', [$userId]);
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
        $latest_post = $latest_post->inRandomOrder()  
        ->take($per_page_limit)
        ->skip($offset)
        ->get()->makeHidden('user_taging');
        $latest_post = $latest_post->shuffle();
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
                $is_Like = true;
                
            }else{
                $is_Like = false;
              
            }
            $latest_post[$key]['is_like'] = $is_Like;
            $UserComment = Comment::where('feed_id',$item['id'])->get()->toArray(); 
            $users = UserModel::where('id', $item['user_id'])->first()->toArray();
            // dd($users);
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


        return array('status' => 'Success', 'user_group_post' => $latest_post->toArray()); 
    }

        public function removeGroup(Request $request){
            $group = Group::find($request->group_id);
           if(!$group){ 
               return array('status' => 'failed', 'message' => 'Group not found'); 
           }
           $group->delete();
           return array('status' => 'Success', 'message' => 'Group Deleted Sucessful');
        }
        public function leaveUsersFromGroup(Request $request){
            // dd(auth('sanctum')->user()->id);
            $validateUser = Validator::make(
                $request->all(),
                [
                    'group_id' => 'required', 
                    'role' => ['required', 'in:moderator,creater_admin,sub_admin,member'], 
                ],
                [
                    'role.required' => 'The role field is required.',
                    'role.in' => 'Invalid role', 
                    'group_id.required' => 'The group_id field is required.', 
                ]
            ); 
            if ($validateUser->fails()) { 
                return response()->json(['status' => 'failed', 'message' => $validateUser->errors()->first()]); 
            }

           $auth = auth('sanctum')->user()->id; 
            $userRole = $request->role;  
            $group_id = $request->group_id;  
            $group = Group::find($group_id);

            switch ($userRole) {
                case 'moderator': 
                 $moderators =  json_decode($group['moderators'], true); 
                 if($moderators != null){
                 if(count($moderators) > 0){ 
                 $key = array_search($auth, $moderators);
                 if ($key !== false) {
                     unset($moderators[$key]);
                 } 
                 $moderators = array_values($moderators);
                 if(count($moderators) > 0){
                    $group->moderators = json_encode($moderators,JSON_FORCE_OBJECT); 
                    $group->save();  
                    return response()->json(['status' => 'Success', 'users' => 'moderator leaves']);
                 }else{
                    $group->moderators = null; 
                    $group->save();  
                    return response()->json(['status' => 'Success', 'users' => 'moderator leaves']);
                 } 
                 

             }else{
                 return response()->json(['status' => 'failed', 'message' => 'not find in moderators list']); 
             }  
            } else{
                return response()->json(['status' => 'failed', 'message' => 'not find in moderators list']); 
            } 
                    break;





                case 'creater_admin':
                   
                    
                    $sub_admin =  json_decode($group['admins'], true); 
                    // dd($sub_admin[1]);
                    // die();
                    if(isset($sub_admin[0])){ 
                        $creater_admin = (int)$sub_admin[1];
                      $sub_admin =  [(string)($sub_admin[1])];
                       $group->creator_id = $creater_admin;
                        $group->admins = json_encode($sub_admin,JSON_FORCE_OBJECT);
                        $group->save();
                    return response()->json(['status' => 'Success', 'users' => 'creater admin leaves']);

                        
                    }else{
                        return response()->json(['status' => 'failed', 'message' => 'creater_admin not find']);
                    } 


                    
                    break;


                case 'member': 
                    $members =  json_decode($group['members'], true); 
                    if(count($members) > 0){ 
                    $key = array_search($auth, $members);
                    if ($key !== false) {
                        unset($members[$key]);
                    } 
                    $members = array_values($members); 
                    $group->members = json_encode($members,JSON_FORCE_OBJECT); 
                    $group->save();  
                    return response()->json(['status' => 'Success', 'users' => 'member leaves']);
                }else{
                    return response()->json(['status' => 'failed', 'message' => 'not find in member list']); 
                }
                    break;



                case 'sub_admin':  
                    $sub_admin =  json_decode($group['admins'], true); 
                    if(isset($sub_admin[1])){ 
                      $sub_admin =  [(string)($sub_admin[0])];
                        $group->admins = json_encode($sub_admin,JSON_FORCE_OBJECT);
                        $group->save();
                    return response()->json(['status' => 'Success', 'users' => 'sub_admin leaves']);

                        
                    }else{
                        return response()->json(['status' => 'failed', 'message' => 'sub-admin not find']);
                    } 
                   
                    break;
                default:
                   
                    break;
            }

        }


        public function join_group_request(Request $request){
           
            $group = Group::find($request->group_id);
            $user_id = (string)auth('sanctum')->User()->id;
            if($group->group_status == 1){ 
                $requested_user  = json_decode($group['requested_user'], true);  
                if($requested_user){
                $check = array_search($user_id,$requested_user);
                
                if( $check !== false){
                 unset($requested_user[$check]);
                $data = array_values($requested_user);
                $group->requested_user = json_encode($data, JSON_FORCE_OBJECT);
                $group->save();
                return response()->json(['status' => 'Success', 'message' => 'revoke-request success']);
                }else{
                    $user_id = (string)auth('sanctum')->User()->id;
                    if(!$group->requested_user){
                        $auth = [(string)auth('sanctum')->User()->id];
                        $group->requested_user = json_encode($auth, JSON_FORCE_OBJECT);
                        $group->save();
                        return response()->json(['status' => 'Success', 'message' => 'request add auccessful']);
                    }
                    $requested_user  = json_decode($group['requested_user'], true); 
                    if(in_array($user_id,$requested_user)){
                        return response()->json(['status' => 'failed', 'message' => 'already requested']);
                    }
                  
                      array_push($requested_user,$user_id); 
                      $group->requested_user = json_encode($requested_user, JSON_FORCE_OBJECT);
                      $group->save();
                      return response()->json(['status' => 'Success', 'message' => 'request add auccessful']);
                }
            }else{
                    $user_id = (string)auth('sanctum')->User()->id;
                    if(!$group->requested_user){
                        $auth = [(string)auth('sanctum')->User()->id];
                        $group->requested_user = json_encode($auth, JSON_FORCE_OBJECT);
                        $group->save();
                        return response()->json(['status' => 'Success', 'message' => 'request add auccessful']);
                    }
                    $requested_user  = json_decode($group['requested_user'], true); 
                    if(in_array($user_id,$requested_user)){
                        return response()->json(['status' => 'failed', 'message' => 'already requested']);
                    }
                    // dd($user_id);
                      array_push($requested_user,$user_id); 
                      $group->requested_user = json_encode($requested_user, JSON_FORCE_OBJECT);
                      $group->save();
                      return response()->json(['status' => 'Success', 'message' => 'request add auccessful']);
                } 
           

            }else{
                $requested_user  = json_decode($group['requested_user'], true);
                $check = false;
                if($requested_user){
                    $check = array_search($user_id,$requested_user); 
                } 
                if( $check !== false){
                 unset($requested_user[$check]);
                $data = array_values($requested_user);
                $group->requested_user = json_encode($data, JSON_FORCE_OBJECT);
                $group->save();
                return response()->json(['status' => 'Success', 'message' => 'revoke-request success']);
                }else
                { 
                    $user_id = (string)auth('sanctum')->User()->id;
                    if(!$group->members){
                        $auth = [(string)auth('sanctum')->User()->id];
                        $group->members = json_encode($auth, JSON_FORCE_OBJECT);
                        $group->save();
                        return response()->json(['status' => 'Success', 'message' => 'Join successful']);
                    }
                    $requested_user  = json_decode($group['members'], true); 
                    if(in_array($user_id,$requested_user)){
                        return response()->json(['status' => 'failed', 'message' => 'already Joined']);
                    } 
                      array_push($requested_user,$user_id); 
                      $group->members = json_encode($requested_user, JSON_FORCE_OBJECT);
                      $group->save();
                      return response()->json(['status' => 'Success', 'message' => 'Join successful']); 

                } 
               
            }
        }



        public function revoke_request(Request $request){
            $user_id = (string)auth('sanctum')->user()->id;
            // dd($user_id);
           $group = Group::find($request->group_id);
           $requested_user  = json_decode($group['requested_user'], true);
           $check = array_search($user_id,$requested_user);
           if( $check !== false){
            unset($requested_user[$check]);
           $data = array_values($requested_user);
           $group->requested_user = json_encode($data, JSON_FORCE_OBJECT);
           $group->save();
           return response()->json(['status' => 'Success', 'message' => 'revoke-request success']);
           }else{
            return response()->json(['status' => 'failed', 'message' => 'id not find']);
           }
        } 

        public function joined_groups_all(Request $request){
            $per_page_limit = $request->per_page;
            $page_no = $request->page_no;
            $offset = ($page_no - 1) * $per_page_limit;
           $auth = auth('sanctum')->user()->id; 
        // dd($auth);

             $check = array();
            $groups = Group::all();
            foreach($groups as $key=> $group){
                $members =  json_decode($group['members'], true);  
                if($members || $members !== null){ 
                    if(array_search(auth('sanctum')->user()->id,$members)){
                        array_push($check,$group->id);
                    }
                }
                
                $moderators =  json_decode($group['moderators'], true); 
                if($moderators || $moderators !== null){
                    if(array_search(auth('sanctum')->user()->id,$moderators)){
                        array_push($check,$group->id);
                    }
                }

                $admins =  json_decode($group['admins'], true); 
                if($admins || $admins !== null){
                    if(isset($admins[1])){
                        // dd($admins[1]);
                        $sub_admin = $admins[1];
                        if($sub_admin ==  $auth){
                            array_push($check,$group->id);
                        }
                    
                } 
            }
           

            };

            $check = array_values($check);
            
           $all_groups = Group::whereIn('id',$check)
           ->take($per_page_limit)
            ->skip($offset)
            ->get()->toArray();


           if(!$all_groups){
            return response()->json(['status' => 'Success', 'groups' => []]);
           }
           foreach($all_groups as $keys => $item){
            $members   = json_decode($item['members'],true); 
            $admins   = json_decode($item['admins'],true); 
            $moderators   = json_decode($item['moderators'],true); 
            if(!Empty($members)){
                $member_count = count($members);
            }else{
                $member_count = 0;
            }
            if(!Empty($moderators)){
                $moderators_count = count($moderators);
            }else{
                $moderators_count = 0;
            }
           $all_groups[$keys]['members_count'] = ($member_count + $moderators_count + count($admins));
        }

           return array('status' => 'Success', 'groups' => $all_groups); 
 
 
    }


        public function auth_req_view_group(Request $request){
        //    dd(auth('sanctum')->user()->id);
            $group_id = $request->group_id;
            $per_page_limit = $request->per_page;
            $page_no = $request->page_no;
            $offset = ($page_no - 1) * $per_page_limit;
            $auth = auth('sanctum')->user()->id;
           
            $auth_groups = Group::where('id',$group_id)->first()->makeHidden('members','admins','moderators','created_at','updated_at');  
            if(!$auth_groups){ 
            return array('status' => 'Success', 'groups' => []); 
           } 
           $transformedMembers = [];
           if($auth_groups->requested_user){  
           $requested_user = json_decode($auth_groups->requested_user, true); 
           foreach ($requested_user as $key => $requested_userId) {
            $user = UserModel::select('id', 'is_private','subscription_verified', 'full_name', 'username', 'pro_img', 'country', 'city', 'check_in_points')->find($requested_userId);
            if ($user) {
                $user->pro_img = $user->pro_img ? asset('public/admin-assets/img/users/' . $user->pro_img) : null;
                $transformedMembers[$key] = $user;
            }

            $location = CheckIn::where('user_id', $requested_userId)->where(['is_checkout_trip' => 0, 'verified_location' => 1])->first();
            if ($location) {
                $updatedLocation = CheckInLocation::where('user_id', $user->id)->where(['check_country' => $location->country, 'check_city' => $location->city])->first();
                if ($updatedLocation) {
                    $user->country = $updatedLocation->check_country;
                    $user->city = $updatedLocation->check_city;
                } else {
                    $user->country = "not checkIn";
                    $user->city = "not checkIn";
                }
            } else { 
                $user->country = "not checkIn";
                $user->city = "not checkIn";
            }
        } 
    }
            $auth_groups->requested_user = $transformedMembers; 
            $paginatedUsers = array_slice($auth_groups['requested_user'], $offset, $per_page_limit); 
            $auth_groups->requested_user = $paginatedUsers; 
            return array('status' => 'Success', 'groups' => $auth_groups);  
        }

        public function auth_req_view_update(Request $request){
            // dd('ok');
            $auth = auth('sanctum')->user()->id; 
            $group_id = $request->group_id; 
            $user_id = $request->user_id; 
            $group = Group::find($group_id);
            if(!$group){
                return response()->json(['status' => 'failed', 'message' => 'group not found']); 
            }

 
            if($request->update == '1'){
              $members = json_decode($group->members,true);
              $requested_user = json_decode($group->requested_user,true);
               $key = array_search($user_id,$requested_user);
                if($key !== false){
                    unset($requested_user[$key]);
                    $data = array_values($requested_user);
                    array_push($members,$user_id);
                    $group->requested_user  = json_encode($data,JSON_FORCE_OBJECT);
                    $group->members  = json_encode($members,JSON_FORCE_OBJECT);
                $group->save();
                return array('status' => 'Success', 'groups' => 'accepted success');
                }else{
                    return response()->json(['status' => 'failed', 'message' => 'group not found']); 

                }
               
              
            }
            if($request->update == '0'){
                $requested_user = json_decode($group->requested_user,true);
                $key = array_search($user_id,$requested_user);
                 if($key !== false){
                     unset($requested_user[$key]);
                     $data = array_values($requested_user); 
                     $group->requested_user  = json_encode($data,JSON_FORCE_OBJECT);
                 $group->save();
                 return array('status' => 'Success', 'groups' => 'canceled success');
                 }else{
                     return response()->json(['status' => 'failed', 'message' => 'group not found']); 
 
                 }
            }
            else{
                return response()->json(['status' => 'failed', 'message' => 'group not found']); 
            }
           
        }
    
}
