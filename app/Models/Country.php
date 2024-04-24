<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $table = 'countries';
    protected $fillable = [
        'country_name',
        'badges',
        
    ];

    public function cityName()
    {
        return $this->belongsTo(Country::class, 'country_name');
    }

    public function getBadgesAttribute($value)
    {
    $allowedRoutes = ['select_user_location_api'];

    if (in_array(request()->route()->getName(), $allowedRoutes)) { 
        return $value ? asset('public/admin-assets/img/country/country_badges/' . $value) : null;
    } 

    return $value;
    }
}
