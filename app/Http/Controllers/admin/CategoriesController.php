<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\City;
use DB;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends Controller
{
    public function showCountry()
    {
        $countries = Country::orderBy('id', 'desc')->get();
        return view('admin.category.country', compact('countries'));
    }

    public function insertCountry(Request $request)
    {
        // dd($request->all());
        $country = new Country();
        $country->country_name = $request->country_name;
        if ($request->hasFile('flag')) {
            $file = $request->File('flag');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/country', $filename);
            $country->flag = $filename;
        }
        if ($request->hasFile('badges')) {
            $file = $request->File('badges');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/country/country_badges', $filename);
            $country->badges = $filename;
        }
        $country->save();
        return back()->with('flash-success', 'You Have Successfully Added A Country.');
    }

    public function edit_country(Request $request)
    {
        $formdata = $request->all();
        $hidden_country_id = $formdata['hidden_country_id'];
        $edit_country = Country::find($hidden_country_id);
        $existflag = 'public/admin-assets/img/country/' . $edit_country->flag;
        $existbadges = 'public/admin-assets/img/country/country_badges/' . $edit_country->badges;
        $edit_country->country_name = $formdata['country_name'];
        if ($request->hasFile('flag')) {
            if (Storage::exists($existflag)) {
                Storage::delete($existflag);
            }
            $file = $request->File('flag');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/country', $filename);
            $edit_country->flag = $filename;
        }
        if ($request->hasFile('badges')) {
            if (Storage::exists($existbadges)) {
                Storage::delete($existbadges);
            }
            $file = $request->File('badges');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/country/country_badges', $filename);
            $edit_country->badges = $filename;
        }
        $edit_country->save();
        if ($edit_country->id > 0) {
            return back()->with('flash-success', 'You Have Successfully Updated Country.');
        } else {
            return back()->with('flash-error', 'something went wrong.');
        }
    }

    public function showCitys()
    {
        $countrys = Country::orderBy('id', 'desc')->get();
        // $citys = City::orderBy('id', 'desc')->join('countries', 'countries.id', '=', 'City.country_name')->select('City.*', 'countries.country_name')->get();
        $citys = DB::table('cities')->orderBy('id', 'desc')->join('countries', 'countries.id', '=', 'cities.country_name')->select('cities.*', 'countries.country_name')->get();
        return view('admin.category.city', compact('citys', 'countrys'));
    }

    public function insertCity(Request $request)
    {
        $citys = new City();
        $citys->country_name    = $request->country_name;
        $citys->city_name       = $request->city_name;
        // $citys->city_temprature = $request->city_temprature;
        if ($request->hasFile('city_badges')) {
            $file = $request->File('city_badges');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/city/', $filename);
            $citys->city_badges   = $filename;
        }
        $citys->save();
        if ($citys->id > 0) {
            return back()->with('flash-success', 'You have successfully submit city.');
        } else {
            return back()->with('flash-error', 'something went wrong.');
        }
    }

    public function editCity(Request $request)
    {
        $formdata = $request->all();
        $hidden_child_id = $formdata['hidden_child_id'];
        $edit_city = City::find($hidden_child_id);
        $existbadges = 'public/admin-assets/img/city/' . $edit_city->city_badges;
        $edit_city->country_name    = $formdata['country_name'];
        $edit_city->city_name       = $formdata['city_name'];
        // $edit_city->city_temprature = $formdata['city_temprature'];
        if ($request->hasFile('city_badges')) {
            if (Storage::exists($existbadges)) {
                Storage::delete($existbadges);
            }
            $file = $request->File('city_badges');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/city/', $filename);
            $edit_city->city_badges = $filename;
        }
        $edit_city->save();
        if ($edit_city->id > 0) {
            return back()->with('flash-success', 'You have successfully updated city.');
        } else {
            return back()->with('flash-error', 'something went wrong.');
        }
    }

    public function uploadCsvData(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv',
        ]);

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');

            // Generate a unique file name to prevent conflicts
            $filename = time() . '.' . $file->getClientOriginalName();

            // Store the uploaded file in the storage/app/public directory
            $filePath = $file->storeAs('public/admin-assets/csv', $filename);

            // Get the file path
            $filePath = storage_path('app/' . $filePath);

            // Open the CSV file
            $file = fopen($filePath, 'r');

            // date for create_at and update_at column 
            $dates = date('Y-m-d H:i:s');
            
            // Skip header row if needed (for example, if the first row contains column names)
            fgetcsv($file);
            
            // Read and insert each row of data into the database
            while (($row = fgetcsv($file)) !== false) {
                DB::table('cities')->insert([
                    'country_name' => $row[0], // Replace with your column names
                    'city_name'    => $row[1],
                    'city_badges'  => $row[2],
                    'created_at'   => $dates,
                    'updated_at'   => $dates,
                    // Add more columns as needed and map them with CSV row data
                ]);
            }
            fclose($file);
            return redirect()->back()->with('flash-success', 'CSV file uploaded and data inserted into the database.');
        }else{
            return redirect()->back()->with('flash-error', 'pleaste select file.');
        }
        return redirect()->back()->with('flash-error', 'Failed to upload CSV file.');
    }
}
