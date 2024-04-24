<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\UserModel;
use App\Models\ExpLevel;
use App\Models\Country;
use App\Models\Comment;
use App\Models\CommentResponse;
use App\Models\CheckInLocation;
use App\Models\City;
use App\Models\Follow;
use App\Models\UserLike;
use App\Models\Group;
use App\Models\Feed;
use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDO;
use Auth;
use Session;

class seeAllApiController extends Controller
{

    private function calculateStage($checkInPoints)
    {
        $stages = [
            0, 100, 1108.16, 2116.32, 3124.48, 4132.64, 5140.80, 6148.96, 7157.12, 8165.28, 9173.44, 10181.60, 11189.76, 12197.92, 13206.08, 14214.24, 15222.40, 16230.56, 17238.72, 18246.88, 19255.04, 20263.20, 21271.36, 22279.52, 23287.68, 24295.84, 25304.00, 26312.16, 27320.32, 28328.48, 29336.64, 30344.80, 31352.96, 32361.12, 33369.28, 34377.44, 35385.60, 36393.76, 37401.92, 38410.08, 39418.24, 40426.40, 41434.56, 42442.72, 43450.88, 44459.04, 45467.20, 46475.36, 47483.52, 48491.68, 49499.84, 50508.00, 51516.16, 52524.32, 53532.48, 54540.64, 55548.80, 56556.96, 57565.12, 58573.28, 59581.44, 60589.60, 61597.76, 62605.92, 63614.08, 64622.24, 65630.40, 66638.56, 67646.72, 68654.88, 69663.04, 70671.20, 71679.36, 72687.52, 73695.68, 74703.84, 75712.00, 76, 720.16, 77, 728.32, 78, 736.48, 79744.64, 80752.80, 81760.96, 82769.12, 83777.28, 84785.44, 85793.60, 86801.76, 87809.92, 88818.08, 89826.24, 90834.40, 91842.56, 92850.72, 93858.88, 94867.04, 95875.20, 96883.36, 97891.52, 98899.68
        ];

        foreach ($stages as $index => $threshold) {
            if ($checkInPoints < $threshold) {
                return $index;
            }
        }

        return count($stages) - 1;
    }
    public function select_user_explore_location_api(Request $request)
    {
        $auth = auth('sanctum')->user()->id;
        $currentDate = Carbon::now()->format('Y-m-d');
        $per_page_limit = $request->per_page;
        $page_no = $request->page_no;
        $offset = ($page_no - 1) * $per_page_limit;
        if (trim($request->location) !== 'null') {
            $CountryOnly =  Country::where('country_name', trim($request->location))->take(1)->skip(0)->get()->toArray();


            if ($CountryOnly) {
                $data = [array(
                    "id" => $CountryOnly[0]['id'],
                    "country_name" => [
                        "id" => $CountryOnly[0]['id'],
                        "country_name" => $CountryOnly[0]['country_name'],
                        "badges" => $CountryOnly[0]['badges'],
                        "created_at" => "2023-12-16T01:24:51.000000Z",
                        "updated_at" => "2023-12-16T01:24:51.000000Z"
                    ],
                    "city_name" => null,
                    "city_badges" => null,
                    "created_at" => null,
                    "updated_at" => null,
                    "popularity" => false
                )];

                $CityOnly = City::where('country_name', $CountryOnly[0]['id'])->with('countryName')->take($per_page_limit)->skip($offset)->get()->toArray();

                foreach ($CityOnly as $key => $item) {
                    $CityOnly[$key]['popularity'] = false;
                }
                if ($offset <= 0) {
                    $location = array_merge($data, $CityOnly);
                    $array_data = array_values($location);
                    return array('status' => 'Success', 'seeAllLocation' => $array_data);
                }
                $location = $CityOnly;
                $array_data = array_values($location);
                return array('status' => 'Success', 'seeAllLocation' => $array_data);
            }


            $city =  City::where('city_name', 'like', '%' .  $request->location . '%')->with('countryName')->take($per_page_limit)->skip($offset)->get()->toArray();
            $Country =  Country::where('country_name',  'like', '%' . $request->location . '%')->take($per_page_limit)->skip($offset)->get()->toArray();

            //   dd($Country);
            $city_wise = [];
            $country_wise = [];
            if ($city) {

                foreach ($city as $key => $val) {
                    $city[$key]['popularity'] = false;

                    // $city[$key]['country_name']['badges'] = asset('public/admin-assets/img/city/' . $city[0]['city_badges']);
                    // dd($city[$key]['country_name']['badges']);
                    // $city[$key]['country_name']['badges'] = null;
                    // $city[$key]['country_name']['country_name'] = null;

                }
                $city_wise = $city;
                // return array('status' => 'Success', 'seeAllLocation' => $city);
            }
            if ($Country) {

                $city_ =  City::where('country_name', $Country[0]['id'])->with('countryName')->first()->toArray();

                $city_['country_name']['badges'] =  asset('public/admin-assets/img/country/country_badges/' . $Country[0]['badges']);
                $city_['city_name'] =  null;
                $city_['city_badges'] =  null;
                $city_['popularity'] =  false;
                $country_wise = [$city_];
                // return array('status' => 'Success', 'seeAllLocation' => [$city_]);
            }
            // dd($country_wise,$city_wise);
            $location = array_merge($country_wise, $city_wise);
            return array('status' => 'Success', 'seeAllLocation' => $location);
        }
        // details of explore location 
        $checkCities =  CheckIn::select('city', DB::raw('COUNT(*) as total_check_ins'))->orderBy('total_check_ins', 'desc')->groupBy('city')->take(50)->pluck('city')->toArray();
        // dd($checkCities);
        // $cities = City::where('id', '>',0)->with('countryName')->take($per_page_limit)->skip($offset)->get(); 
        $cities = City::where('id', '>', 0)->with('countryName')->get();


        $cities->each(function ($city) use ($checkCities) {
            // $city->cityImages = asset('public/admin-assets/img/city/' . $city->city_badges);  
            $city_search = array_search($city->city_name, $checkCities);
            if ($city_search) {
                $city->popularity = true;
            } else {
                $city->popularity = false;
            }
        });
        $data = $cities->toArray();

        $collection = collect($data);
        $sortedData = $collection->sortByDesc('popularity')->values()->all();
        $popularData = collect($sortedData)->filter(function ($item) {
            return $item['popularity'] === true;
        })->all();

        $remainingData = collect(array_slice($sortedData, count($popularData)));

        $shuffledRemainingData = $remainingData->shuffle()->all();

        $result = array_merge($popularData, $shuffledRemainingData);

        $data_sort = array_slice($result, $offset, $per_page_limit);



        // dd($cities->toArray());
        return array('status' => 'Success', 'seeAllLocation' => $data_sort);
    }
    public function see_all_leader_board_api(Request $request)
    {
        // dd('ok');
        // dd('ok');
        $auth = auth('sanctum')->user()->id;
        $currentDate = Carbon::now()->format('Y-m-d');
        $per_page_limit = $request->per_page;
        $page_no = $request->page_no;
        $offset = ($page_no - 1) * $per_page_limit;
        $location = $request->location;
        $status = $request->status;

        //    $users = UserModel::where('check_in_points', '!=', '0');
        $users = UserModel::where('id', '>', '0');




        if ($location !== 'null') {
            $currentDate = now()->toDateString();
            $query = CheckIn::where('verified_location', 1)
                ->whereDate('start_date', '<=', $currentDate)
                ->whereDate('end_date', '>=', $currentDate)
                ->where('country', 'like', '%' . $location . '%');
            $results = $query->get()->toArray();
            $user_id = array_map(function ($item) {
                return $item['user_id'];
            }, $results);

            if ($user_id) {
                $users->WhereIn('id', $user_id);
            } else {
                return array('status' => 'Success', 'seeAllLeaderboard' => []);
            }
        }

        if ($status !== 'null') {
            $users->Where('type_of_traveler', 'like', '%' . $status . '%');
        }
        $users = $users->orderBY('check_in_points', 'desc')->select('id', 'full_name', 'username', 'pro_img', 'is_private', 'check_in_points', 'subscription_verified', 'type_of_traveler')
            ->with(['checkInsUser' => function ($query) {
                $query->select('id', 'user_id', 'start_date', 'end_date', 'country', 'city', 'status', 'verified_location');
            }])->get();


        $rank = 1;
        $prevCheckInPoints = null;
        $users->each(function ($user) use (&$rank, &$prevCheckInPoints) {

            if ($prevCheckInPoints !== null && $user->check_in_points == $prevCheckInPoints) {
                //    dd('ok',$rank);
                $rank--;
                $user->rank = $rank;
            } else {
                $user->rank = $rank;
            }
            // dd($user->check_in_points);
            //    $user->rank = $rank;
            $subscription_verified = $user->subscription_verified;
            if ($subscription_verified == 'false') {
                $user->subscription_verified = false;
            } else {
                $user->subscription_verified = true;
            }


            $user->localevel = $this->calculateStage($user->check_in_points);
            $prevCheckInPoints = $user->check_in_points;
            $rank++;
        });

        $paginatedUsers = $users->slice($offset)->take($per_page_limit)->toArray();
        $paginatedUsers = array_values($paginatedUsers);
        return array('status' => 'Success', 'seeAllLeaderboard' => $paginatedUsers);
    }
    public function see_all_groups_api(Request $request)
    {
        // dd('ok');
        $auth = auth('sanctum')->user()->id;
        $currentDate = Carbon::now()->format('Y-m-d');
        $per_page_limit = $request->per_page;
        $page_no = $request->page_no;
        $offset = ($page_no - 1) * $per_page_limit;

        $country = $request->country;
        $city = $request->city;
        $name = $request->name;


        $groups = Group::orderBy('id', 'desc');
        if ($city !== 'null' || $country !== 'null') {


            if ($city !== 'null' && $country !== 'null') {
                $groups->where('group_city', 'like', '%' . $city . '%')
                    ->Where('group_country', 'like', '%' . $country . '%');
            } else if (($city === 'null' && $country !== 'null')) {
                $groups->where('group_country', 'like', '%' . $country . '%');
            } else if (($city !== 'null' && $country === 'null')) {
                // dd('ok');
                $groups->Where('group_city', 'like', '%' . $city . '%');
            } else {
                return array('status' => 'Success', 'seeAllgroups' => []);
            }
        }
        if (($name !== 'null')) {
            // dd('ok');
            $groups->Where('title', 'like', '%' . $name . '%');
        }


        $groups = $groups->take($per_page_limit)->skip($offset)->get();

        $groups->each(function ($group) {

            $members = json_decode($group->members, true);
            $admins = json_decode($group->admins, true);
            $moderators = json_decode($group->moderators, true);

            $role = 'user';
            $is_join = false;
            $is_request = false;

            // dd(auth('sanctum')->user()->id);
            $requested_user = json_decode($group->requested_user, true);
            if ($requested_user) {
                if (in_array($group->creator_id, $requested_user)) {
                    $is_request = true;
                } else {
                    $is_request = false;
                }
            } else {
                $is_request = false;
            }


            if ($admins) {
                $admin_count = count($admins);
                if (count($admins) == 2) {
                    if (auth('sanctum')->user()->id == $admins[0]) {
                        $role = 'creater_admin';
                        $is_join = true;
                    }
                    if (auth('sanctum')->user()->id == $admins[1]) {
                        $role = 'sub_admin';
                        $is_join = true;
                    }
                } else {
                    if (in_array(auth('sanctum')->user()->id, $admins)) {
                        $role = 'creater_admin';
                        $is_join = true;
                    }
                }
            }

            if (!Empty($moderators)) {
                $moderators_count = count($moderators);
                if (in_array(auth('sanctum')->user()->id, $moderators)) {
                    $role = 'moderator';
                    $is_join = true;
                }
            }else{
                $moderators_count = 0;
            }

            if (!Empty($members)) {
                $members_count = count($members);
                if (in_array(auth('sanctum')->user()->id, $members)) {
                    $is_join = true;
                    $role = 'member';
                }
            }else{
                $members_count = 0;
            }

            $countMember =  json_decode($group->members);
            $group->members = ($admin_count + $moderators_count + $members_count);
            $group->is_private = $group->group_status;
            $group->is_request = $is_request;
            $group->is_join = $is_join;
            $group->role = $role;
        });
        $groups->makeHidden('group_status');

        return array('status' => 'Success', 'seeAllgroups' => $groups);
    }
    public function my_groups_api(Request $request)
    {

        // dd('ok');
        $per_page_limit = $request->per_page;
        $page_no = $request->page_no;
        $creator_id = Auth::guard('sanctum')->user()->id;
        $offset = ($page_no - 1) * $per_page_limit;
        $groups = Group::where('creator_id', $creator_id)->orderBy('id', 'desc')->take($per_page_limit)->skip($offset)->get();
        //  dd($groups);
        $groups->each(function ($group) {
            $countMember =  json_decode($group->members);
            if(!Empty($countMember)){
                $member_count = count((array)$countMember);
            }else{
                $member_count = 0;
            }
            $moderators =  json_decode($group->moderators);
            $admins =  json_decode($group->admins);
            if(!Empty($moderators)){
                $moderators_count = count((array)$moderators);
            }else{
                $moderators_count = 0;
            }
            $group->members = ($member_count + $moderators_count + count((array)$admins));
            $group->is_private = $group->group_status;
        });
        $groups->makeHidden('group_status');

        return array('status' => 'Success', 'myAllGroups' => $groups);
    }
    public function seeAllmeetPeople(Request $request)
    {
        $auth = auth('sanctum')->user()->id;
       
        $per_page_limit = $request->per_page;
        $page_no = $request->page_no;
        $offset = ($page_no - 1) * $per_page_limit;
        $city = $request->city;
        $country = $request->country;
        $status = $request->status;
        $name = $request->name;
        $users = UserModel::where('id', '!=', $auth);
        if ($city !== 'null' || $country !== 'null') {
            $query = CheckIn::where('verified_location', 1);


            if ($city !== 'null' && $country !== 'null') {

                $query->where(function ($query) use ($city, $country) {
                    $query->where('city', 'like', '%' . $city . '%')
                        ->Where('country', 'like', '%' . $country . '%');
                });
            } else if (($city === 'null' && $country !== 'null')) {
                $query->where(function ($query) use ($country) {
                    $query->Where('country', 'like', '%' . $country . '%');
                });
            } else if (($city !== 'null' && $country === 'null')) {
                $query->where(function ($query) use ($city) {
                    $query->where('city', 'like', '%' . $city . '%');
                });
            }

            $results = $query->get()->toArray();
            if ($results) {
                $user_id = array_map(function ($item) {
                    return $item['user_id'];
                }, $results);
                if ($user_id) {
                    // dd($user_id);
                    $users->whereIn('id', $user_id);
                }
            } else {
                return array('status' => 'Success', 'seeAllmeetPeople' => []);
            }
        }


        if ($status !== 'null') {
            $users->where('type_of_traveler', 'like', '%' . $status . '%');
        }

        if ($name != 'null') {
            $users->where(function ($query) use ($name) {
                $query->where('full_name', 'like', '%' . $name . '%')
                    ->orWhere('username', 'like', '%' . $name . '%');
            });
        }


        $meets = $users->select('id', 'gender', 'is_private', 'subscription_verified', 'full_name', 'username', 'pro_img', 'check_in_points', 'subscription_verified', 'type_of_traveler')->orderBy('check_in_points', 'desc')
            ->with(['checkInsUser' => function ($query) {
                $query->select('id', 'user_id', 'start_date', 'end_date', 'country', 'city', 'status', 'verified_location');
            }])
            ->take($per_page_limit)->skip($offset)->get();
        // dd(count($meets));
        $meets->each(function ($meet) use ($auth) {
            $subscription_verified = $meet->subscription_verified;
            if ($subscription_verified == 'false') {
                $meet->subscription_verified = false;
            } else {
                $meet->subscription_verified = true;
            }
            
            $meet->localevel = $this->calculateStage($meet->check_in_points);
            $location = CheckInLocation::where('user_id', $meet->id)->latest()->first();
            
            $status = null;
            $isFollowing = Follow::where('user_id', $auth)->where('following_id', $meet->id)->where('status', '1')->where('accept', '1')->exists();
            $isRequested = Follow::where('user_id', $auth)->where('following_id', $meet->id)->where('status', '0')->where('accept', '0')->exists();
            if ($isRequested) {
                $status = 0;
            }
            if ($isFollowing) {
                $status = 1;
            }

            $meet->is_following = $isFollowing ? true : false;
            $meet->is_Requested = $isRequested ? true : false;
            $meet->status = $status;

            $followedUsers = Follow::where('user_id', $meet->id)->where('status',1)->where('accept',1)->pluck('following_id')->toArray() ?? [];
            // dd($followedUsers);

            $followersmy = Follow::where('user_id', $auth)->where('status',1)->where('accept',1)
                ->pluck('following_id')->toArray() ?? [];
               
                $diff =  array_intersect($followedUsers, $followersmy);
                // dd($diff,$followedUsers);
        //    echo "<pre>"; print_r($followedUsers); echo "<br>";
            $meet->mutual_follow = count($diff);
        });
// die();
        // if (!$meets->toArray()) {
        //     return array('status' => 'Fail', 'seeAllLocation' => 'data not found');
        // }

        return array('status' => 'Success', 'seeAllmeetPeople' => $meets);
    }

    public function countryBadgesSeeAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   =>   'required|exists:user_models,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $checkCountrys = CheckIn::where('user_id', $request->user_id)->with('countryNames')->select('country')->groupBy('country')->take($per_page_limit)->skip($offset)->get();
        $usersss = UserModel::orderBy('id', 'desc');
        foreach ($checkCountrys as $key => $checkCountry) {
            $cd = $checkCountry->country;
            $checkCountrys[$key]['countryNames']['countryImage'] = asset('public/admin-assets/img/country/country_badges/' . $checkCountry->countryNames->badges);
            $checkCountrys[$key]['countryVisiting'] = CheckIn::where('country', $checkCountry->country)->count();
            $totalConUsers = $usersss->count();
            $visitedUsersCountrys = CheckIn::where('country', $cd)->select('country', DB::raw('count(*) as total'))->groupBy('country')->get()->toArray();
            foreach ($visitedUsersCountrys as $userkey => $usersCountrys) {
                $checkCountrys[$key]['totalPercentageCountryUsers'] = $totalConUsers * $usersCountrys['total'] / 100;
            }
        }
        return response()->json(['status' => 'Success', 'data' => $checkCountrys]);
    }

    public function cityBadgesSeeAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   =>   'required|exists:user_models,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $checkCitys = CheckIn::where('user_id', $request->user_id)->with('cityNames')->select('city')->groupBy('city')->take($per_page_limit)->skip($offset)->get();
        $usersss = UserModel::orderBy('id', 'desc');
        foreach ($checkCitys as $key => $checkCity) {
            $ct = $checkCity->city;
            $checkCitys[$key]['cityVisiting'] = CheckIn::where('city', $checkCity['city'])->count();
            $checkCitys[$key]['cityNames']['cityimgs'] = asset('public/admin-assets/img/city/' . $checkCity['cityNames']['city_badges']);
            $totalCityUsers = $usersss->count();
            $visitedUsersCitys = CheckIn::where('city', $ct)->select('city', DB::raw('count(*) as total'))->groupBy('city')->get()->toArray();
            foreach ($visitedUsersCitys as $visitkey => $usersCitysValue) {
                $checkCitys[$key]['totalPercentageCityUsers'] = ($totalCityUsers * $usersCitysValue['total']) / 100;
            }
        }
        return response()->json(['status' => 'Success', 'data' => $checkCitys]);
    }

    public function othersSeeAllAlbum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   =>   'required|exists:user_models,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $auth = $request->user_id;
        // $myfeed = array();
        $myfeeds = Feed::where('user_id', $auth)->where('is_text', 'false')->orderBy('id', 'desc')->take($per_page_limit)->skip($offset)->get()->makeHidden('image');
        $islikeds = UserLike::where("user_id", $auth)->pluck('feed_id')->toArray();
        // dd($islikeds);
        foreach ($myfeeds as $key => $feeds) {
            $today = now();
            $formatted_dt1 = Carbon::parse($feeds->created_at);
            $formatted_dt2 = Carbon::parse($today);
            $date_diff = $formatted_dt1->diffInDays($formatted_dt2);
            $myfeeds[$key]['date_diff'] = $date_diff;
            $myfeeds[$key]['like'] = UserLike::where('feed_id', $feeds->id)->count();
            $myfeeds[$key]['Comment'] = Comment::where('feed_id', $feeds->id)->count();
            $decodes = json_decode($feeds->image, true);
            foreach ($decodes as $value) {
                $myfeed[] = asset('public/admin-assets/img/user_feeds/' . $value);
            }

            if (in_array($feeds->id, $islikeds)) {
                $myfeeds[$key]['isLiked'] = true;
            } else {
                $myfeeds[$key]['isLiked'] = false;
            }

            $date = Carbon::parse($feeds->created_at);
            $formattedDate = $date->format('j M, Y');
            $myfeeds[$key]['date'] =  $formattedDate;

            $user_detailsss = UserModel::where('id', $auth)->get();
            $user_detailsss->each(function ($single) {
                if ($single->pro_img) {
                    $single->pro_img = asset('public/admin-assets/img/users/' . $single->pro_img);
                } else {
                    $single->pro_img = null;
                }
            });
            $myfeeds[$key]['user_details'] = $user_detailsss;
        }
        return array('status' => 'Success', 'data' => $myfeeds);
    }

    public function otherTravelJaurnySeeAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   =>   'required|exists:user_models,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;
        $usersss = UserModel::orderBy('id', 'desc');

        $auth = $request->user_id;
        $pastTravel = CheckIn::where('user_id', $auth)->orderBy('id', 'desc')->with('countryNames', function ($query) {
            $query->select('id', 'country_name');
        })->with('cityNames', function ($querys) {
            $querys->select('id', 'city_name', 'city_badges');
        })->take($per_page_limit)->skip($offset)->get();
        foreach ($pastTravel as $keysss => $past) {
            $pastTravel[$keysss]['cityNames']['city_url'] = asset('public/admin-assets/img/city/' . $past['cityNames']['city_badges']);
            //======================= geting between days start or end date =========================
            $formatted_dt1 = Carbon::parse($past->start_date);
            $formatted_dt2 = Carbon::parse($past->end_date);
            $date_diff = $formatted_dt1->diffInDays($formatted_dt2);
            $pastTravel[$keysss]['days'] = $date_diff;
            $data[] = $past->country;
            //======================= past travel users data =========================================
            $amountOfUsers = CheckIn::where('user_id', '!=', $auth)->where('city', $past->city)->pluck('user_id')->all();
            $pastTravel[$keysss]['x_more_users'] = count($amountOfUsers);
            $getUsers = UserModel::whereIn('id', $amountOfUsers)->select('id', 'full_name', 'username', 'pro_img')->get();
            foreach ($getUsers as $keyid => $getuser) {
                if($getuser->pro_img){
                    $getuser->pro_img = asset('public/admin-assets/img/users/' . $getuser->pro_img);
                }else{
                    $getuser->pro_img = null;
                }
            }
            $pastTravel[$keysss]['usersDetails'] = $getUsers;
        }
        return array('status' => 'Success', 'data' => $pastTravel);
    }
}
