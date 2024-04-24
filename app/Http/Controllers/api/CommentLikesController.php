<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommentLikes;
use App\Models\UserLike;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\ResponseCommentLikes;
use App\Models\CommentResponse;
use App\Models\Follow;
use App\Models\UserModel;
use DB;
use Illuminate\Support\Facades\Validator;


class CommentLikesController extends Controller
{
    public function feedLikesList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'required',
            'page_no' => 'required',
            'feedId'  => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $auth = auth('sanctum')->user()->id;
        $likes = UserLike::where(['feed_id' => $request->feedId, 'like_unlike' => '1'])->with('fromUser', function ($query) {
            $query->select('id', 'username', 'pro_img', 'subscription_verified', 'is_private', 'full_name');
        })->with('toUser', function ($query) {
            $query->select('id', 'username', 'subscription_verified', 'is_private', 'full_name');
        })->get()->take($per_page_limit)->skip($offset);
        $likes->each(function ($like) use ($auth) {
            // dd($like['fromUser']['subscription_verified']);
            if ($like['fromUser']['subscription_verified'] == 'true') {
                $like['fromUser']['subscription_verified'] = true;
            } else {
                $like['fromUser']['subscription_verified'] = false;
            }
            if ($like['fromUser']['pro_img'] != null) {
                $like['fromUser']['profile_img'] = asset('public/admin-assets/img/users/' . $like['fromUser']['pro_img']);
            } else {
                $like['fromUser']['profile_img'] = null;
            }
            $checkfollow = Follow::where('user_id', $auth)->where('following_id', $like->user_id)->first();
            if ($checkfollow) {
                $like['fromUser']['is_following'] = true;
            } else {
                $like['fromUser']['is_following'] = false;
            }
        });
        return array('status' => 'Success', 'data' => $likes);
    }

    public function commentList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'required',
            'page_no' => 'required',
            'feedId' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $auth = auth('sanctum')->user()->id;
        $comments = Comment::where('feed_id', $request->feedId)->with(['fromUser:id,full_name,username,subscription_verified,is_private,pro_img', 'toUser:id,full_name,username,subscription_verified,is_private,pro_img'])->get();
        foreach ($comments as $key => $comment) {
            if ($comment->tag_user_id != null) {
                $tagUserss = json_decode($comment->tag_user_id, true);
                $taginguser = []; // Initialize the array inside the loop
                foreach ($tagUserss as $tagkey => $tagUsers) {
                    $usersss = UserModel::where('id', $tagUsers)->select('id', 'full_name', 'username', 'subscription_verified', 'is_private', 'pro_img')->first();
                    // $taginguser[] = UserModel::where('id', $tagUsers)->select('id', 'full_name', 'username', 'subscription_verified', 'is_private', 'pro_img')->first();
                    if ($usersss) { // Check if user exists
                        if ($usersss->pro_img != null) {
                            $usersss->pro_img = asset('public/admin-assets/img/users/' . $usersss->pro_img);
                        } else {
                            $usersss->pro_img = null; // Or provide a default image URL
                        }
                        $taginguser[] = $usersss; // Add the user to the array
                    }
                }
                $comments[$key]['tagUsers'] = $taginguser;
            } else {
                $comments[$key]['tagUsers'] = null;
            }
            $comments[$key]['comment_Img'] = asset('public/admin-assets/img/comment/' . $comment->image);
            if ($comment['fromUser']['pro_img']) {
                $comments[$key]['fromUser']['profile_img'] = asset('public/admin-assets/img/users/' . $comment['fromUser']['pro_img']);
            }

            //========= user comments likes =========
            $comments[$key]['commentsLikes'] = (int)CommentLike::where(['likes' => 1, 'comment_id' => $comment->id])->count();
            $checklikesss = CommentLike::where(['user_id' => $auth, 'comment_id' => $comment->id])->first();
            if ($checklikesss  != null) {
                if($checklikesss->likes == 1){
                    $comments[$key]['is_likess'] = true;
                }else{
                    $comments[$key]['is_likess'] = false;
                }
            } else {
                $comments[$key]['is_likess'] = false;
            }

            //========= response details ========= 
            $commentResponse = CommentResponse::where('comment_id', $comment->id)->with('userDetails', function ($query) {
                $query->select('id', 'username', 'pro_img', 'subscription_verified', 'is_private', 'full_name');
            })->get();
            foreach ($commentResponse as $responsekey => $commentRespons) {
                $res_comm_tag_user = []; // Initialize the array inside the loop
                if ($commentRespons->response_tag_user_id != null) {
                    $responseTagedUsers = json_decode($commentRespons->response_tag_user_id, true);
                    // dd($responseTagedUsers);
                    foreach ($responseTagedUsers as $responseTage => $responseTaggedUser) {
                        // $res_comm_tag_user[] = UserModel::where('id', $responseTagedUser)->select('id', 'full_name', 'username', 'subscription_verified', 'is_private', 'pro_img')->first();
                        $user = UserModel::where('id', $responseTaggedUser)->select('id', 'full_name', 'username', 'subscription_verified', 'is_private', 'pro_img')->first();
                        if ($user) { // Check if user exists
                            if ($user->pro_img != null) {
                                $user->res_comme_tag_img = asset('public/admin-assets/img/users/' . $user->pro_img);
                            } else {
                                $user->res_comme_tag_img = asset('public/admin-assets/img/users/');
                            }
                            $res_comm_tag_user[] = $user; // Add the user to the array
                        }
                    }
                } else {
                    $responseTagedUsers = null;
                }
                $commentResponse[$responsekey]['response_comment_taged_users']  = $res_comm_tag_user;
                $commentResponse[$responsekey]['userDetails']['responsUserImage'] = asset('public/admin-assets/img/users/' . $commentRespons['userDetails']['pro_img']);

                //========= user response comments likes =========
                $commentResponse[$responsekey]['responseCommentsLikes'] = (int)ResponseCommentLikes::where(['likes' => 1, 'response_comment_id' => $commentRespons->id])->count();

                $checklikes = ResponseCommentLikes::where(['user_id' => $auth, 'comment_id' => $commentRespons->comment_id, 'response_comment_id' => $commentRespons->id])->first();
                if ($checklikes != null) {
                    if($checklikes->likes == 1){
                        $commentResponse[$responsekey]['is_response_like'] = true;
                    }else{
                        $commentResponse[$responsekey]['is_response_like'] = false;
                    }
                } else {
                    $commentResponse[$responsekey]['is_response_like'] = false;
                }
            }
            $comments[$key]['commentResponse'] = $commentResponse;
            $comments[$key]['totalResponse'] = sizeof($commentResponse);
        }
        return array('status' => 'Success', 'data' => $comments);
    }

    public function likesComments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' =>  'required|exists:comments,id',
            'likes'      =>  'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $auth = auth('sanctum')->user()->id;
        $checkUser = CommentLike::where(['user_id' => $auth, 'comment_id' => $request->comment_id])->first();
        if ($checkUser && $request->likes == '0') {
            $update = DB::table('comment_likes')->where(['user_id' => $auth, 'comment_id' => $request->comment_id])->update(['likes' => $request->likes]);
            return array('status' => 'Success', 'message' => 'Unliked Successfully');
        } else if ($checkUser && $request->likes == '1') {
            $update = DB::table('comment_likes')->where(['user_id' => $auth, 'comment_id' => $request->comment_id])->update(['likes' => $request->likes]);
            return array('status' => 'Success', 'message' => 'Again liked Successfully');
        } else {
            $likes = new CommentLike();
            $likes->user_id = $auth;
            $likes->comment_id = $request->comment_id;
            $likes->likes = $request->likes;
            $likes->save();
        }
        return array('status' => 'Success', 'message' => 'Comment liked Successfully');
    }

    public function lekeResponseComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id'          =>  'required|exists:comments,id',
            'response_comment_id' =>  'required|exists:comment_responses,id',
            'likes'               =>  'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $auth = auth('sanctum')->user()->id;
        $checkUser = ResponseCommentLikes::where(['user_id' => $auth, 'comment_id' => $request->comment_id, 'response_comment_id' => $request->response_comment_id])->first();
        if ($checkUser && $request->likes == '0') {
            $update = DB::table('response_comment_likes')->where(['user_id' => $auth, 'comment_id' => $checkUser->comment_id, 'response_comment_id' => $request->response_comment_id])->update(['likes' => $request->likes]);
            return array('status' => 'Success', 'message' => 'Unliked Successfully');
        } else if ($checkUser && $request->likes == '1') {
            $update = DB::table('response_comment_likes')->where(['user_id' => $auth, 'comment_id' => $checkUser->comment_id, 'response_comment_id' => $request->response_comment_id])->update(['likes' => $request->likes]);
            return array('status' => 'Success', 'message' => 'Again liked Successfully');
        } else {
            $likes = new ResponseCommentLikes();
            $likes->user_id = $auth;
            $likes->comment_id = $request->comment_id;
            $likes->response_comment_id = $request->response_comment_id;
            $likes->likes = $request->likes;
            $likes->save();
        }
        return array('status' => 'Success', 'message' => 'Comment liked Successfully');
    }
}
