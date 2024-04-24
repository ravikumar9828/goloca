<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use App\Models\UserModel;
use App\Models\goloca_price;

class goloca_priceController extends Controller
{
    public function get_goloca_prize_list(){
        $users_list = goloca_price::orderBy('id', 'desc')->get();
        // dd($users_list);
        return view('admin/goloca_prize/goloca_prize', compact('users_list'));
    }

    public function insert_goloca_prize(Request $request){
        $formdata = $request->all(); //dd($formdata); 
        $user = new goloca_price;
        $user->title = $request->title;
        $user->description = $request->description;
        $user->min_goloca_level = $request->min_goloca_level;
        $user->min_participants = $request->min_participants; 
        $user->start_date = $request->start_date;
        $user->end_date = $request->end_date;
        $user->status = $request->user_status;
        if ($request->hasFile('pro_img')) {
            $file = $request->file('pro_img');
            $extension = $file->getClientOriginalExtension();
            $filename = time().'.'.$extension;
            $file->move('public/admin-assets/img/goloca_prize', $filename);
            $user->image = $filename;
        }
        $user->save();

        if($user->id > 0){
           
            return back()->with('flash-success', 'You Have Successfully Added A goloca Prize.');
        } else{
            return back()->with('flash-error', 'something went wrong.');
        }
    }

   


    public function edit_goloca_prize(Request $request){
        $formdata = $request->all(); //dd($formdata);
        $hidden_user_id = $formdata['hidden_user_id'];

        $edit_user = goloca_price::find($hidden_user_id);
        // dd($edit_user,$formdata);

        $edit_user->title =   $formdata['title']; 
        $edit_user->description =  $formdata['description']; 
        $edit_user->min_goloca_level =  $formdata['min_goloca_level'];  
        $edit_user->min_participants =   $formdata['min_participants']; 
        $edit_user->start_date =  $formdata['start_date']; 
        $edit_user->end_date =  $formdata['end_date']; 
        if ($request->hasFile('pro_img')) {
            $file = $request->file('pro_img');
            $extension = $file->getClientOriginalExtension();
            $filename = time().'.'.$extension;
            $file->move('public/admin-assets/img/goloca_prize', $filename);
            $edit_user->image = $filename;
        }
        
        $edit_user->status = $formdata['user_status'];
        $edit_user->save();
        //Last inserted ID
        if($edit_user->id > 0){
            return back()->with('flash-success', 'You Have Successfully Updated goloca Prize.');
        } else{
            return back()->with('flash-error', 'something went wrong.');
        }
    }


    public function delete_goloca_prize($id){
        //dd($id);
        $data = goloca_price::find($id)->delete();
        if($data){
            return back()->with('flash-success', 'You Have Successfully Deleted A User.');
        } else{
            return back()->with('flash-error', 'something went wrong.');
        }
    }
}
