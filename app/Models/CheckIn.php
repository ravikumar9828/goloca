<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    use HasFactory;

    public function countryNames()
    {
        return $this->belongsTo(Country::class, 'country', 'country_name'); 
    }

    public function cityNames()
    {
        return $this->belongsTo(City::class, 'city', 'city_name');
    }
    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }
}
