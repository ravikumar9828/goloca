<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feed;
use Illuminate\Support\Facades\Validator;

class FeedsController extends Controller
{
    public function usersFeeds(Request $request)
    {
      

        if($request->group_id == 'null'){
            $validator = Validator::make($request->all(), [
                'image'       => 'required',
                'country'    => 'required',    
                'city'       => 'required',    
            ]); 
            if ($validator->fails()) {
                return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
            }
        }
        if($request->is_text == 'false'){
        $validator = Validator::make($request->all(), [
            'image'       => 'required',
             
        ]); 
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        } 
    }
        // dd($request->all(),$request->city);
 
        $auth = auth('sanctum')->user()->id;
        $feeds = new Feed();
        $feeds->user_id      = $auth;
        $feeds->description  = $request->description;
        if($request->user_taging == ''){
            $expld = null; 
        }else{
            $expld = explode(',',$request->user_taging);
        }
        $feeds->user_taging      = json_encode($expld, JSON_FORCE_OBJECT);
        if($request->group_id == '0'){ 
            $feeds->country      = $request->country;
            $feeds->city         = $request->city;
            $feeds->address      = $request->address;
         }
         if($request->is_text == 'false'){
        $files = array();
        if($request->hasFile('image')){
            $images = $request->file('image');
            foreach ($images as  $value) {
                $name = time().'_'.$value->getClientOriginalName();
                $value->move('public/admin-assets/img/user_feeds', $name);
                $files[] = $name;
            }
        }
        $feeds->image = json_encode($files, JSON_FORCE_OBJECT);
    }
            if($request->group_id != '0'){
                $feeds->group_id = $request->group_id;
            }
          
        $feeds->is_text = $request->is_text;
        if($request->is_text == 'true'){
            $files[] = '1704374929_download.jpg';
            $feeds->image = json_encode($files, JSON_FORCE_OBJECT);
        }
         
        $feeds->save();
        if($feeds->id > '0'){
            return array('status' => 'Success', 'message' => 'Feeds post successfully');
        }else{
            return array('status' => 'Failed', 'message' => 'Somthing went wrong');
        }
    }

    public function show_user_feeds()
    {
        $auth = auth('sanctum')->user()->id;
        $feeds = Feed::where('user_id', $auth)->orderBy('id', 'desc')->with('userDetails', function($query){
            $query->select('id', 'username', 'pro_img', 'created_at')->get();
        })->get()->makeHidden(['image','user_taging']);

        foreach ($feeds as $key => $user_img) {
            if($feeds[$key]['is_text'] == 'true'){
                $feeds[$key]['is_text'] = true;
            }else if($feeds[$key]['is_text'] == 'false'){
                $feeds[$key]['is_text'] = false;
            }
            $feeds[$key]['userDetails']['pro_img_url'] = asset('public/admin-assets/img/users/'.$user_img->userDetails->pro_img);
        }
        return array('status' => 'Success', 'data' => $feeds);
    }

    public function deleteFeeds(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'feed_id'  =>  'required',   
        ]); 
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $auth = auth('sanctum')->user()->id;
        $feeds = Feed::where('user_id', $auth)->where('id', $request->feed_id)->delete();
        if($feeds){
            return response()->json(['status' => 'Success', 'message' => 'Feeds delete successfully']);
        }else{
            return response()->json(['status' => 'Failed', 'message' => 'OOPS! something went wrong']);
        }
    }

    public function editFeeds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feed_id'       => 'required',
             
        ]); 
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        } 
        $feeds = Feed::find($request->feed_id);
        if(!$feeds){
            return response()->json(['status' => 'Failed', 'message' => 'post_id does not find']);
        } 
        $feeds->description = $request->description;
        $feeds->save();
        return response()->json(['status' => 'Success', 'message' => 'Feeds update successfully']);
    }
}
