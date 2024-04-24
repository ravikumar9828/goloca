<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;

class goloca_price extends Model
{
    use HasFactory;
    protected $table = 'goloca_prize';
    protected $fillable = ['title','description','min_goloca_level','image','min_participants','total_participants','participants_userId','start_date','end_date','status'];


   

    public function getStartDateAttribute($value)
    {
        $date = new Carbon($value);
        return $date->format('d M, Y');
    }

    public function getEndDateAttribute($value)
    {
        if ($value === null) {
            return null;
        }
        $date = new Carbon($value);
        return $date->format('d M, Y');
    }


    public function getImageAttribute($value)
    {
    $allowedRoutes = ['golocaPrizes','selectedGolocaPrizes','participateGolocaPrizes','cancelGolocaPrizes'];

    if (in_array(request()->route()->getName(), $allowedRoutes)) { 
        return $value ? asset('public/admin-assets/img/goloca_prize/' . $value) : null;
    } 

    return $value;
    }

   

}
