<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    public function following()
    {
        return $this->hasOne(UserModel::class, 'id', 'following_id');
    }

    public function follower()
    {
        return $this->hasOne(UserModel::class, 'id', 'user_id');
    }
}
