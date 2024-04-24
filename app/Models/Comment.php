<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    public function fromUser()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(UserModel::class,'commented_user_id');
    }
}
