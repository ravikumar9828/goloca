<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\UserModel;

class CouponController extends Controller
{
    public function couponCode()
    {
        $users = UserModel::orderBy('id', 'desc')->get();
        $coupons = Coupon::orderBy('id', 'desc')->get();
        return view('admin.coupons.coupon', compact('coupons', 'users'));
    }

    public function createCoupon(Request $request)
    {
        $coupon = new Coupon();
        $coupon->type     = $request->type;
        $coupon->value     = $request->value;
        $coupon->quantity = $request->quantity;
        $coupon->start    = $request->start;
        $coupon->end      = $request->end;
        $coupon->coupon   = $request->coupon;
        $coupon->status   = $request->status;
        $coupon->save();
        if($coupon->id > 0){
            return back()->with('flash-success', 'You have successfully create coupon.');
        } else{
            return back()->with('flash-error', 'something went wrong.');
        }
    }

    public function editCouponCode(Request $request)
    {
        $formdata = $request->all();
        $hidden_coupon_id = $formdata['hidden_coupon_id'];

        $edit_coupon = Coupon::find($hidden_coupon_id);
        $edit_coupon->type = $formdata['type'];
        $edit_coupon->value = $formdata['value'];
        $edit_coupon->quantity = $formdata['quantity'];
        $edit_coupon->start = $formdata['start'];
        $edit_coupon->end = $formdata['end'];
        $edit_coupon->coupon = $formdata['coupon'];
        $edit_coupon->status = $formdata['status'];
        $edit_coupon->save();
        if($edit_coupon->id > 0){
            return back()->with('flash-success', 'You Have Successfully Updated User.');
        } else{
            return back()->with('flash-error', 'something went wrong.');
        }
    }

    public function couponDelete($id)
    {
        $coupon = Coupon::find($id)->delete();
        if($coupon){
            return back()->with('flash-success', 'You have successfully deleted a coupon.');
        } else{
            return back()->with('flash-error', 'something went wrong.');
        }
    }


}
