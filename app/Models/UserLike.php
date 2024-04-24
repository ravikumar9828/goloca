<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'feed_id',
        'liked_user_id',
        'like_unlike',
    ];

    public function fromUser()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(UserModel::class, 'liked_user_id');
    }
}
