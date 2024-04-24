<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
 

class UserModel extends Authenticatable
{

    use HasFactory, HasApiTokens, Notifiable;
    protected $table = 'user_models';

    // protected $appends = ['profile_url'];

    protected $fillable = [
        'full_name',
        'email',
        'username',
        'password',
        'pro_img',
        'is_block',
    ];
   

    // ============== users following ===============

    public function following()
    {
        return $this->belongsToMany(UserModel::class, 'follows', 'following_id', 'user_id')->withTimestamps();
    }

    // ============== users followers ===============

    public function followers()
    {
        return $this->belongsToMany(UserModel::class, 'follows', 'user_id', 'following_id')->withTimestamps();
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    

   

    public function checkIns()
    {
        $currentDate = now()->toDateString();

        return $this->hasMany(CheckIn::class, 'user_id');
                    // ->whereDate('start_date', '<=', $currentDate)
                    // ->whereDate('end_date', '>=', $currentDate);
                    // ->where('verified_location', 1);
    }
    public function checkInsUser()
    {
        $currentDate = now()->toDateString();

        return $this->hasMany(CheckIn::class, 'user_id')
                    ->whereDate('start_date', '<=', $currentDate)
                    ->whereDate('end_date', '>=', $currentDate)
                    ->where('verified_location', 1);
    }
    public function getProImgAttribute($value)
    {
    $allowedRoutes = ['user_home_post','see_all_leader_board_api','seeAllmeetPeople','exploreGroup','addMembers','user_group_post','show_user_profile','showOtherUserDeatils','other_pro_textFeed_seeAll','my_pro_textFeed_seeAll','socialite_login'];

    if (in_array(request()->route()->getName(), $allowedRoutes)) { 
        return $value ? asset('public/admin-assets/img/users/' . $value) : null;
    } 

    return $value;
    }


    
   
    
  
}
