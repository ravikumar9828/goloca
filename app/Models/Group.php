<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $table = 'groups';

    protected $fillable = [
        'creator_id',
        'title',
        'group_country',
        'group_city',
        'image',
        'group_status'
        
    ];

    public function groupCountryNames()
    {
        return $this->belongsTo(Country::class, 'group_country'); 
    }

    public function groupCityNames()
    {
        return $this->belongsTo(City::class, 'group_city', 'id');
    }


 

    public function getImageAttribute($value)
    {
    $allowedRoutes = ['my_groups_api','see_all_groups_api','exploreGroup','joined_groups_all','auth_req_view_group'];

    if (in_array(request()->route()->getName(), $allowedRoutes)) { 
        return $value ? asset('public/admin-assets/img/group/' . $value) : null;
    } 

    return $value;
    }

   
}
