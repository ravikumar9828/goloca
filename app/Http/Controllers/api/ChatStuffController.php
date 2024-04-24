<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserModel;
use App\Models\UserChatBlocked;
use Illuminate\Support\Facades\Validator;

class ChatStuffController extends Controller
{
    public function chatUserBlocked(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'blocked_id'      => 'required',
            'blocked_unblocked'       => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $auth = auth('sanctum')->User()->id;
        $data = new UserChatBlocked();
        $data->user_id = $auth;
        $data->blocked_user_id   = $request->blocked_id;
        $data->blocked_unblocked = $request->blocked_unblocked;
        $data->save();
        return response()->json(['status' => 'Success', 'message' => "User blocked successfully"]);
    }
}
