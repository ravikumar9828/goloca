<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;
use App\Models\Group;
use App\Models\CheckIn;
use App\Models\UserModel;
use App\Models\Follow; 
use App\Models\Feed; 
use App\Models\CheckInLocation;
use App\Models\UserLike;
use App\Models\Comment;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Validator;

trait notification
{
  
  public function notify($token,$conclution){
  
  
   $value = [
      "to" => $token->device_token,
      "notification" => [
          "body" => $conclution,
      ]
  ];

  $dataString = json_encode($value);
  $headers = [
      'Authorization: key=' . $SERVER_API_KEY,
      'Content-Type: application/json',
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
  // Disabling SSL Certificate support temporarly
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

  $result = curl_exec($ch);
  if ($result === FALSE) {
      die('Curl failed: ' . curl_error($ch));
  }
  curl_close($ch);
  }
    
}
