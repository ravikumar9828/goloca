<?php

use App\Http\Controllers\api\ChatStuffController;
use App\Http\Controllers\api\CommentLikesController;
use App\Http\Controllers\api\GolocaUserController;
use App\Http\Controllers\api\HomeController;
use App\Http\Controllers\api\FeedsController;
use App\Http\Controllers\api\GroupController;
use App\Http\Controllers\api\testController;
use App\Http\Controllers\api\locationController;
use App\Http\Controllers\api\seeAllApiController;
use App\Http\Controllers\api\golocaPrizeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//  Route::get('/clear-cache', function () {
//     Artisan::call('cache:clear');
//     Artisan::call('route:clear');
//     Artisan::call('view:clear');
//     Artisan::call('config:clear');
// 	$controllerName = 'api/seeAllApiController'; // Replace with your desired controller name
//     Artisan::call('make:controller', ['name' => $controllerName]);
//     return "cache is clear";
// });

Route::post('socialite-login', [testController::class, 'socialite_login'])->name('socialite_login');
Route::middleware('apiAuthentication:sanctum')->group( function () {
    //================================= Start ravi make api===============================================================
    Route::post('user-home-post',[testController::class,'user_home_post'])->name('user_home_post');  
    Route::post('see-all-explore-location-api',[seeAllApiController::class,'select_user_explore_location_api'])->name('select_user_location_api');  
    Route::post('see-all-leader-board-api',[seeAllApiController::class,'see_all_leader_board_api'])->name('see_all_leader_board_api');  
    Route::post('see-all-groups-api',[seeAllApiController::class,'see_all_groups_api'])->name('see_all_groups_api');  
    Route::post('my-groups-api',[seeAllApiController::class,'my_groups_api'])->name('my_groups_api');  
    Route::post('user-see-all-meetpeoples',[seeAllApiController::class,'seeAllmeetPeople'])->name('seeAllmeetPeople');





    Route::post('all-golocaPrizes',[golocaPrizeController::class,'golocaPrizes'])->name('golocaPrizes');
    Route::post('selected-golocaPrizes',[golocaPrizeController::class,'selectedGolocaPrizes'])->name('selectedGolocaPrizes');
    Route::post('participate-golocaPrizes',[golocaPrizeController::class,'participateGolocaPrizes'])->name('participateGolocaPrizes');
    Route::post('cancel-golocaPrizes',[golocaPrizeController::class,'cancelGolocaPrizes'])->name('cancelGolocaPrizes'); 

    //=================================== End ravi make api===============================================================
    

    //============================================ Goloca user authentication ==========================================
    Route::post('user-update-profile',[GolocaUserController::class,'user_profile_update']);
    Route::get('user-show-profile',[GolocaUserController::class,'show_user_profile'])->name('show_user_profile');
    Route::post('user-logout',[GolocaUserController::class,'userLogout']);
    Route::get('user-delete-account',[GolocaUserController::class,'user_delete_account']);
    Route::get('user-list',[GolocaUserController::class,'userList']);
    Route::post('show-other-user-details',[GolocaUserController::class,'showOtherUserDeatils'])->name('showOtherUserDeatils');
    Route::post('show-earn-badges',[GolocaUserController::class,'showEarnBadges']);
    Route::get('user-remove-profile-picture',[GolocaUserController::class,'removeProfilePicture']);
    Route::post('user-block',[GolocaUserController::class,'user_block'])->name('user_block');
    Route::get('user-block-list',[GolocaUserController::class,'user_block_list'])->name('user_block_list');

    //============================================ Goloca see all api's routs ===========================================
    Route::post('user-see-all-countries',[GolocaUserController::class,'seeAllCountries']);
    Route::post('user-see-all-city',[GolocaUserController::class,'seeAllCity']);
    Route::post('user-see-images-videos',[GolocaUserController::class,'seeAllImagesVideos']);
    Route::post('user-see-travel-journy',[GolocaUserController::class,'seeAllTravelJourny']);
    Route::post('other-profile-textFeed-seeAll',[GolocaUserController::class,'other_pro_textFeed_seeAll'])->name('other_pro_textFeed_seeAll');
    Route::post('my-profile-textFeed-seeAll',[GolocaUserController::class,'my_pro_textFeed_seeAll'])->name('my_pro_textFeed_seeAll');

    //============================================ seeAll controller stuff =============================================
    Route::post('user-see-all-country-badges',[seeAllApiController::class,'countryBadgesSeeAll']);
    Route::post('user-see-all-city-badges',[seeAllApiController::class,'cityBadgesSeeAll']);
    Route::post('user-see-all-others-album',[seeAllApiController::class,'othersSeeAllAlbum']);
    Route::post('user-see-all-others-travel-jaurny',[seeAllApiController::class,'otherTravelJaurnySeeAll']);

    //============================================ Home see all api's ====================================================
    Route::post('user-see-all-leaderbord',[HomeController::class,'seeAllLeader']);
    Route::post('user-see-all-location',[HomeController::class,'seeAllLocation']);
    Route::post('user-see-all-groups',[HomeController::class,'seeAllGroups']);

    //============================================ Goloca Home Controller ===============================================
    Route::get('user-home-page',[HomeController::class,'homePage']);
    Route::post('user-check-in',[HomeController::class,'checkIn']);
    Route::post('user-update-our-checkIn',[HomeController::class,'updateCheckIn']);
    // Route::post('user-check-verified-location',[HomeController::class,'checkVerifiedLocation']);
    Route::get('user-show-checked-in-list',[HomeController::class,'showCheckedInList']);
    Route::post('user-show-country-list',[HomeController::class,'countryList']);
    Route::post('user-show-perticuler-country',[HomeController::class,'perticulerCountry']);
    Route::post('user-show-city-list',[HomeController::class,'cityList']);
    Route::post('user-show-perticuler-city',[HomeController::class,'perticulerCity']);
    Route::post('user-home',[HomeController::class,'homeController']);
    Route::post('user-search',[HomeController::class,'userSearch']);
    Route::post('user-update-daily-location',[HomeController::class,'dailyUpdateLocation']);
    Route::get('user-check-in-countdown',[HomeController::class,'checkInCountdown']);
    Route::get('user-show-history-checkIn',[HomeController::class,'historyCheckIn']);
    Route::post('user-early-checkout-checkIn',[HomeController::class,'checkOutEarly']);
    Route::post('user-show-selected-location',[HomeController::class,'selectedLocation'])->name('selectedLocation');

    //============================================ Goloca users Feeds ===================================================
    // Route::post('user-post-feeds',[FeedsController::class,'usersFeeds'])->name('usersFeeds');
    Route::post('user-post-feeds',[FeedsController::class,'usersFeeds'])->name('groupFeeds');
    Route::get('user-show-post-feeds',[FeedsController::class,'show_user_feeds']);
    Route::get('loca-stage',[GolocaUserController::class,'localevelStage']);
    Route::post('user-delete-feeds',[FeedsController::class,'deleteFeeds']);
    Route::post('user-edit-feeds',[FeedsController::class,'editFeeds'])->name('editFeeds');

    //=========================================== Goloca users Followers =================================================
    Route::post('user-follow',[HomeController::class,'userFollow']);
    Route::post('user-following',[HomeController::class,'userFollowing']);
    Route::post('user-followers',[HomeController::class,'userFollowers']);
    Route::post('user-remove-followers',[HomeController::class,'followerRemove']);
    Route::post('others-users-followings',[HomeController::class,'otherFollowing']);
    Route::post('others-users-followers',[HomeController::class,'otherFollowers']);
    Route::post('user-accept-requested',[HomeController::class,'acceptRequest']);

    //=========================================== Goloca likes or comment or reports url =================================
    Route::post('user-feeds-likes',[HomeController::class,'userLikes']);
    Route::post('user-comment',[HomeController::class,'upperUserComment']);
    Route::post('user-comment-response',[HomeController::class,'responseComment']);
    Route::post('user-show-likes-list',[CommentLikesController::class,'feedLikesList']);
    Route::post('user-show-comment-list',[CommentLikesController::class,'commentList']);
    Route::post('user-liked-comment',[CommentLikesController::class,'likesComments']);
    Route::post('user-likes-response-comment',[CommentLikesController::class,'lekeResponseComment']);

    //=========================================== Goloca search country =================================================
    Route::post('user-search-country',[HomeController::class,'searchCountry']);
    Route::post('user-search-city',[HomeController::class,'searchCity']);

    //=========================================== Goloca group users ====================================================
    Route::post('user-add-members',[GroupController::class,'addMembers'])->name('addMembers');
    Route::post('user-create-group',[GroupController::class,'createGroup']);
    Route::post('user-show-perticuler-group',[GroupController::class,'perticulerGroup']);
    Route::post('user-edit-groups',[GroupController::class,'editGroup']);
    Route::post('user-exploreGroup',[GroupController::class,'exploreGroup'])->name('exploreGroup');
    Route::post('user-group-post',[GroupController::class,'user_group_post'])->name('user_group_post');
    Route::post('user-exploreGroupMember',[GroupController::class,'exploreGroup'])->name('exploreGroupMember');
    Route::post('remove-group',[GroupController::class,'removeGroup'])->name('removeGroup');
    Route::post('leave-group',[GroupController::class,'leaveUsersFromGroup'])->name('leaveUsersFromGroup');
    Route::post('join-group',[GroupController::class,'join_group_request'])->name('join_group');
    Route::post('joined-groups-all',[GroupController::class,'joined_groups_all'])->name('joined_groups_all');
    Route::post('revoke-request',[GroupController::class,'revoke_request'])->name('revoke_request');
    Route::post('auth-req-view-group',[GroupController::class,'auth_req_view_group'])->name('auth_req_view_group');
    Route::post('auth-req-view-update',[GroupController::class,'auth_req_view_update'])->name('auth_req_view_update');

    //========================================= Chat stuff controller ====================================================
    Route::post('chat-user-blocked',[ChatStuffController::class,'chatUserBlocked']);

    

});

Route::post('users-registration',[GolocaUserController::class,'userRegistration']);
Route::post('users-verify-account',[GolocaUserController::class,'verifyRegisterOtp']);
Route::post('goloca-user-login',[GolocaUserController::class,'userLogin'])->name('userLogin');

Route::post('user-send-otp',[GolocaUserController::class,'userSendOtp']);
Route::post('user-verifyed-otp',[GolocaUserController::class,'verifyOtpForgetPassword']);
Route::post('user-change-forget-password',[GolocaUserController::class,'userChangPassword']);
