<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\UsersController;
use App\Http\Controllers\admin\SubadminController;
use App\Http\Controllers\admin\BlogsController;
use App\Http\Controllers\admin\CategoriesController;
use App\Http\Controllers\admin\PagesController;
use App\Http\Controllers\admin\ParentCategoryController;
use App\Http\Controllers\admin\ChildCategoryController;
use App\Http\Controllers\admin\CouponController;
use App\Http\Controllers\admin\SubChildCategoryController;
use App\Http\Controllers\admin\TravelJourneyController;
use App\Http\Controllers\admin\goloca_priceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

 Route::get('/', function () {
    return view('welcome');
});
Route::view('/privacy-policy','privacy/privacy');
//Non-Logged-in user
Route::group(['middleware' => ['guest']],function(){
    Route::view('admin','admin/auth/login')->name('login1');
    Route::view('admin/auth-login','admin/auth/login')->name('login');
    Route::view('admin/forgot-password', 'admin/auth/forgot-password')->name('admin.forgotpassword');
    Route::post('admin/recover-password', [Admincontroller::class, 'recover_password_func'])->name('admin.recover-password');
    Route::post('admin/reset-password', [Admincontroller::class,'reset_password_func'] )->name('admin.reset-password');


});

Route::get('/reset-password/{token}', function (string $token) {
        return view('admin/auth/reset-password', ['token' => $token]);
    })->middleware('guest')->name('password.reset');


Route::post('admin/user_login', [AdminController::class,'admin_login_func'] )->name('admin.login');

///////Logged-in user(Admin)/////////
Route::group(['middleware' => ['auth:subadmin,web']],function(){

    Route::view('admin/dashboard','admin/dashboard')->name('admin.dashboard');
    Route::get('admin/logout', [AdminController::class,'admin_logout_func'])->name('admin.logout');
    Route::get('admin/profile', [AdminController::class,'get_admin_info_func'])->name('admin.getinfo');
    Route::post('admin/update_admin_info', [Admincontroller::class,'update_admin_generalinfo'])->name('update_admin_info');
    Route::post('admin/update_password', [Admincontroller::class,'update_admin_password_func'])->name('update_admin_password');
    Route::post('admin/upload-images',[AdminController::class,'storeImage'])->name('admin.uploadimage');

    Route::get('admin/all-user', [UsersController::class, 'get_users_list'])->name('all_user');
    Route::post('admin/insert-users', [UsersController::class,'insert_users'])->name('admin.insertuser');
    Route::get('admin/delete-users/{id}',[UsersController::class,'delete_users'])->name('admin.deleteuser');
    Route::post('admin/edit-users', [UsersController::class,'edit_users'])->name('admin.editusers');
    Route::post('admin/check-email-existance',[UsersController::class, 'check_email_existance'])->name('admin.checkemailexistance');

    //======================================= goloca prize routes ==================================================================== 
    Route::get('admin/all-goloca_prize', [goloca_priceController::class, 'get_goloca_prize_list'])->name('get_goloca_prize_list');
    Route::post('admin/insert-goloca_prize', [goloca_priceController::class,'insert_goloca_prize'])->name('admin.insertgoloca_prize');
    Route::get('admin/delete-goloca_prize/{id}',[goloca_priceController::class,'delete_goloca_prize'])->name('admin.deletegoloca_prize');
    Route::post('admin/edit-goloca_prize', [goloca_priceController::class,'edit_goloca_prize'])->name('admin.editgoloca_prize');



    Route::get('admin/all-blogs',[BlogsController::class,'all_blogs_list'])->name('all_blog');
    Route::post('admin/insert-blog', [BlogsController::class, 'insert_blog'])->name('insertblog');
    Route::post('admin/edit-blog', [BlogsController::class, 'edit_blog'])->name('editblog');
    Route::delete('admin/delete-blog/{id}', [BlogsController::class, 'delete_blog'])->name('deleteblog');

    Route::view('admin/all-pages','admin/pages/index')->name('allpageslist');
    Route::get('admin/edit-privacy',[PagesController::class,'editprivacy'])->name('editprivacy');
    Route::post('admin/update-about-page',[PagesController::class,'aboutUs']);
    Route::get('admin/users-hepls',[PagesController::class,'showHelp'])->name('showHelp');
    //Route::post('admin/update-page',[PagesController::class,'updatepages'])->name('updatepages');
    Route::get('admin/show-user-feeds',[PagesController::class,'userFeeds'])->name('userFeeds');
    Route::get('admin/delete-feeds/{id}',[PagesController::class,'deleteFeeds']);
    Route::get('admin/show-gallery/{id}',[PagesController::class,'gallery']);
    Route::get('admin/show-check-in',[PagesController::class,'seeCheckIn'])->name('travelJourney');

    //======================================= coupon routes ====================================================================
    Route::get('admin/show-coupons',[CouponController::class,'couponCode'])->name('couponCode');
    Route::post('admin/create-coupon',[CouponController::class,'createCoupon'])->name('createCoupon');
    Route::post('admin/edit-coupon',[CouponController::class,'editCouponCode']);
    Route::get('admin/delete-coupon/{id}',[CouponController::class,'couponDelete']);

    //======================================= Countries Routes ==================================================================
    Route::get('admin/show-countries',[CategoriesController::class,'showCountry'])->name('showCountry');
    Route::post('admin/insert-countries',[CategoriesController::class,'insertCountry']);
    Route::post('admin/edit-country',[CategoriesController::class,'edit_country']);

    //======================================== Citys Routes ======================================================================
    Route::get('admin/show-city',[CategoriesController::class,'showCitys'])->name('showCitys');
    Route::post('admin/insert-city',[CategoriesController::class,'insertCity']);
    Route::post('admin/edit-city',[CategoriesController::class,'editCity']);
    Route::post('admin/export-data',[CategoriesController::class,'uploadCsvData']);

});
