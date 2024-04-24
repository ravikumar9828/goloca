<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

trait Common_trait
{
    public function create_unique_slug($string = '', $table = '', $field = 'slug', $key = null, $value = null)
    {
        $slug = Str::of($string)->slug('-');
        $slug = strtolower($slug);

        $i = 0;
        $params = array();
        $params[$field] = $slug;
        if ($key) {
            $params["$key !="] = $value;
        }

        while (DB::table($table)->where($params)->count()) {
            if (!preg_match('/-{1}[0-9]+$/', $slug)) {
                $slug .= '-' . ++$i;
            } else {
                $slug = preg_replace('/[0-9]+$/', ++$i, $slug);
            }
            $params[$field] = $slug;
        }
        return $slug;
    }

    public function preabc($data, $status = false)
    {
        echo "<pre>";
        print_r($data);
        if (!$status) {
            die;
        }
    }
    
}
