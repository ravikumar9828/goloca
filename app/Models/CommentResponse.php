<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentResponse extends Model
{
    use HasFactory;

    public function userDetails()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}
