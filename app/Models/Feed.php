<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    use HasFactory;
    protected $fillable = [
        'image',
        'is_text',
    ];

    protected $appends = ['feeds_url', 'users_tag'];

    public function getFeedsUrlAttribute()
    {
        $imagess = json_decode($this->image, true);

        foreach ($imagess as $image) {
            $path[] = asset('public/admin-assets/img/user_feeds/' . $image);
        }
        return $path;
    }

    public function getUsersTagAttribute()
    {

        $userstagings = json_decode($this->user_taging, true);
        $usersData = [];
        if (is_array($userstagings)) {


            foreach ($userstagings as $key => $taging) {
                $user = UserModel::where('id', $taging)->select('id', 'username', 'pro_img')->first();
            $allowedRoutes =['selectedLocation'];
           if (in_array(request()->route()->getName(), $allowedRoutes)) { 
       
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
                        'pro_img' => $img_url,
                    ];
                }
            }
            }
            else
            { 
                if ($user) {
                    $usersData[] = [
                        'id' => $user->id,
                        'username' => $user->username,
                        'pro_img' => $user->pro_img,
                    ];
                }
            }


            }
        }
        return $usersData;
    
    }

    // $sadjfhkjh = asset('public/admin-assets/img/users/'.$user->pro_img);
    

    public function userDetails()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
    public function getIsTextAttribute($value)
    {
        if($value == 'true'){
           return true;
        }
        if($value == 'false'){
            return false;
        }
      
    }
}
