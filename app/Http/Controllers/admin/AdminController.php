<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Rules\MatchAdminOldPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use File;
use App\Models\User;
use App\Models\SubadminModel;

class AdminController extends Controller
{

    public function admin_login_func(Request $request){

        //$data = $request->all();
        //dd($data);

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    public function admin_logout_func(Request $request){

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        //return Redirect()->route('login');
        return Redirect('admin/auth-login');
    }


    public function get_admin_info_func(){
        $GetData['data'] = Auth::user();
        return view('admin/auth/profile', $GetData);
    }
    public function update_admin_generalinfo(Request $request){
        //$data = $request->all();
        //dd($data);
        $validatedData = $request->validate([
                'username' => 'required',
                'email' => 'required|email',
                'mobile' => 'required|max:10'
            ]);

        $User = User::find(1);
        $User->name = $request->username;
        $User->email = $request->email;
        $User->mobile = $request->mobile;

        $User->save();

        return back()->with('flash-success', 'You Have Successfully Update Your Profile.');
    }

    public function update_admin_password_func(Request $request){
        //$allpass = $request->all();
        //dd($allpass);
        $request->validate([
            'current_password' => ['required', new MatchAdminOldPassword],
            'password'         => 'required|same:confirm_password',
            'confirm_password' => 'required_with:password|same:password|min:6'
        ]);
        #Match The Current Password
        // if(!Hash::check($request->current_password, auth()->user()->password)){
        //     return back()->with("flash-error", "Old Password Does Not Match!");
        // }

        #Update the new Password
        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->password)]);

        return back()->with("flash-success", "Password changed successfully!");
    }

    public function storeImage(Request $request){

        //dd(public_path('images'));
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        //$originalname = $request->image->getClientOriginalName();
        $fileextension = $request->image->extension();
        $filenename = 'admin'.'.'.$fileextension;

        //Create directory if not exist
        $path = public_path('/admin-assets/assets/img/profile_img/admin');
        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }
        //Move File
        $request->image->move($path, $filenename);

        $User = User::find(1);
        $User->pro_img = $filenename;
        $User->save();

        return response()->json($path);


    }

    public function recover_password_func(Request $request){

        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink(
            $request->only('email')
        );
        return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
    }

     public function reset_password_func(Request $request){

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);


        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                ? redirect()->route('login')->with('status', __($status))
                : back()->withErrors(['email' => [__($status)]]);
    }

}
