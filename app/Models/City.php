<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $table = 'cities';

    protected $fillable = [
        'country_name',
        'city_name',
        'city_badges'
        
    ];

    public function countryName()
    {
        return $this->belongsTo(Country::class, 'country_name');
    }


    public function getCityBadgesAttribute($value)
    {
    $allowedRoutes = ['select_user_location_api'];

    if (in_array(request()->route()->getName(), $allowedRoutes)) { 
        return $value ? asset('/public/admin-assets/img/city/' . $value) : null;
    } 

    return $value;
    }
}
