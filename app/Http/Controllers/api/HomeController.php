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
use App\Models\Feed;
use App\Models\Group;
use App\Models\UserLike;
use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDO;

class HomeController extends Controller
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
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'country' => 'required',
            'city' => 'required',
            // 'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $auth = auth('sanctum')->user()->id;
        $users = UserModel::where('id', $auth)->first();
        $currentDate = Carbon::now()->format('Y-m-d');

        if ($request->past_trips != 1) {
            $overlappingTrip = CheckIn::where('user_id', $auth)->where('verified_location', '!=', 1)
                ->where(function ($query) use ($request) {
                    $query->where(function ($query) use ($request) {
                        $query->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->start_date);
                    })->orWhere(function ($query) use ($request) {
                        $query->where('start_date', '<=', $request->end_date)
                            ->where('end_date', '>=', $request->end_date);
                    })->orWhere(function ($query) use ($request) {
                        $query->where('start_date', '>=', $request->start_date)
                            ->where('end_date', '<=', $request->end_date);
                    });
                })->orderBy('id', 'desc')->get();
            // dd($overlappingTrip);

            $checkdates = array();
            foreach ($overlappingTrip as $overkey => $overlapping) {
                // dd($overlapping->id);
                $checkdates[] = $overlapping->id;
            }

            if ($checkdates != []) {
                return array('status' => 'Success', 'message' => 'Already exists trip on this slot', 'updateId' => $checkdates);
            } else {
                $checkIn = new CheckIn();
                $checkIn->user_id = $auth;
                $checkIn->start_date = $request->start_date;
                $checkIn->end_date = $request->end_date;
                $checkIn->country = $request->country;
                $checkIn->city = $request->city;
                // $checkIn->status = $request->status;

                $checkIn->save();
                $exp = UserModel::where('id', $auth)->first()->check_in_points;
                $checkExp = ExpLevel::where(['user_id' => $auth, 'reason' => 'Fist time user check In'])->first();
                if ($checkExp == null) {
                    $exp_points = 50;
                    $data = new ExpLevel();
                    $data->user_id = $auth;
                    $data->points = $exp_points;
                    $data->reason = "Fist time user check In";
                    $data->save();
                    UserModel::where('id', $auth)->update(['check_in_points' => $exp + $exp_points]);
                }
                return array('status' => 'Success', 'message' => 'Successfully check-in user');
            }
        } else {
            if ($users->trip_conut < 5 && $users->is_private == 0) {
                $overlappingNonPremiumPastTrip = CheckIn::where('user_id', $auth)->where('past_trips', '1')
                    ->where(function ($query) use ($request) {
                        $query->where(function ($query) use ($request) {
                            $query->where('start_date', '<=', $request->start_date)
                                ->where('end_date', '>=', $request->start_date);
                        })->orWhere(function ($query) use ($request) {
                            $query->where('start_date', '<=', $request->end_date)
                                ->where('end_date', '>=', $request->end_date);
                        })->orWhere(function ($query) use ($request) {
                            $query->where('start_date', '>=', $request->start_date)
                                ->where('end_date', '<=', $request->end_date);
                        });
                    })
                    ->first();
                // dd($overlappingNonPremiumPastTrip);

                if ($overlappingNonPremiumPastTrip) {
                    return array('status' => 'Success', 'message' => 'You can`t update past trips');
                } else {
                    $pastCheckIn = new CheckIn();
                    $pastCheckIn->user_id = $auth;
                    $pastCheckIn->start_date = $request->start_date;
                    $pastCheckIn->end_date = $request->end_date;
                    $pastCheckIn->country = $request->country;
                    $pastCheckIn->city = $request->city;
                    // $pastCheckIn->status = $request->status;
                    $pastCheckIn->past_trips = $request->past_trips;
                    if ($pastCheckIn->save()) {
                        UserModel::where('id', $request->user_id)->update(['trip_conut' => $users->trip_conut + '1']);
                    }
                    return array('status' => 'Success', 'user' => 'Non prime user', 'message' => 'Past trip successfully check-in');
                }
            } else if ($users->is_private == 1 && $users->trip_conut < 20) {
                $overlappingPrimimunPastTrip = CheckIn::where('user_id', $auth)->where('past_trips', '1')
                    ->where(function ($query) use ($request) {
                        $query->where(function ($query) use ($request) {
                            $query->where('start_date', '<=', $request->start_date)
                                ->where('end_date', '>=', $request->start_date);
                        })->orWhere(function ($query) use ($request) {
                            $query->where('start_date', '<=', $request->end_date)
                                ->where('end_date', '>=', $request->end_date);
                        })->orWhere(function ($query) use ($request) {
                            $query->where('start_date', '>=', $request->start_date)
                                ->where('end_date', '<=', $request->end_date);
                        });
                    })
                    ->orderBy('id', 'desc')->first();

                if ($overlappingPrimimunPastTrip) {
                    return array('status' => 'Success', 'user' => 'Non prime user', 'message' => 'Past trip successfully check-in');
                } else {
                    $pastCheckIn = new CheckIn();
                    $pastCheckIn->user_id = $auth;
                    $pastCheckIn->start_date = $request->start_date;
                    $pastCheckIn->end_date = $request->end_date;
                    $pastCheckIn->country = $request->country;
                    $pastCheckIn->city = $request->city;
                    // $pastCheckIn->status = $request->status;
                    $pastCheckIn->past_trips = $request->past_trips;
                    if ($pastCheckIn->save()) {
                        UserModel::where('id', $auth)->update(['trip_conut' => $users->trip_conut + '1']);
                    }
                }
                return array('status' => 'Success', 'user' => 'prime user', 'message' => 'Past trip successfully check-in');
            } else {
                return array('status' => 'Failed', 'message' => 'Your past trip count is over!');
            }
        }
    }

    public function updateCheckIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'updateId'   => 'required',
            'start_date' => 'required',
            'country'   => 'required',
            'city'      => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }


        if ($request->past_trips != 1) {
            $deleteOldTrip = CheckIn::whereIn('id', explode(',', $request->updateId))->delete();
            $auth = auth('sanctum')->user()->id;
            $checkIn = new CheckIn();
            $checkIn->user_id = $auth;
            $checkIn->start_date = $request->start_date;
            $checkIn->end_date = $request->end_date;
            $checkIn->country = $request->country;
            $checkIn->city = $request->city;
            $checkIn->status = $request->status;
            $checkIn->update_trips = '1';
            $checkIn->save();
            return array('status' => 'Success', 'message' => 'Trips update successfully');
        } else {
            return array('status' => 'Failed', 'message' => 'You can`t update past trips');
        }
    }

    public function dailyUpdateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => 'required',
            'city' => 'required',
            'check_date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }
        $auth = auth('sanctum')->user()->id;
        $update = new CheckInLocation();
        $update->user_id = $auth;
        $update->check_country = $request->country;
        $update->check_city = $request->city;
        $update->check_date = $request->check_date;

        $checkdates = CheckInLocation::where(['user_id' => $auth, 'check_country' => $request->country, 'check_city' => $request->city, 'check_date' => $request->check_date])->orderBy('id', 'desc')->first();
        if ($checkdates != null) {
            $diffdays = CheckInLocation::where(['user_id' => $auth, 'check_country' => $request->country, 'check_city' => $request->city])->orderBy('id', 'desc')->first()->visit_count;
        } else {
            $checkdatess = CheckInLocation::where('user_id', $auth)->orderBy('id', 'desc')->first();
            if ($checkdatess != null) {
                if ($checkdatess->check_country == $request->country && $checkdatess->check_city == $request->city) {
                    $lastCheckInDate = date('Y-m-d', strtotime($checkdatess->check_date));
                    $currentDate = date('Y-m-d', strtotime($request->check_date));
                    $date1 = new DateTime($lastCheckInDate);
                    $date2 = new DateTime($currentDate);
                    $interval = $date1->diff($date2);
                    $daysDifference = $interval->days;
                    $remainingCount = CheckInLocation::where(['user_id' => $auth, 'check_country' => $request->country, 'check_city' => $request->city])->orderBy('id', 'desc')->first()->visit_count;
                    $update->visit_count = $daysDifference + $remainingCount;
                    $update->save();
                    $diffdays = $update->visit_count;
                } else {
                    $update->visit_count = 1;
                    $update->save();
                    $diffdays = $update->visit_count;
                }
            } else {
                $update->visit_count = 1;
                $update->save();
                $diffdays = $update->visit_count;
            }
        }

        $countries = Country::where('country_name', $request->country)->first();
        if ($countries) {
            $data = [];
            $data['country'] = $request->country;
            $data['city'] = $request->city;
            $data['diffdays'] = $diffdays;
            $data['flag'] = asset('public/admin-assets/img/country/country_badges/' . $countries->badges);

            $checkIns = DB::table('check_ins')->where('user_id', $auth)->where('verified_location', '!=', '1')->where(['start_date' => $request->check_date, 'country' => $request->country, 'city' => $request->city])->first();

            if ($checkIns) {
                $verifiedDates = date('Y-m-d');
                CheckIn::where('id', $checkIns->id)->update(['verified_location' => '1', 'verified_date' => $verifiedDates]);
            }
            return array('status' => 'Success', 'Message' => 'uploade successfully', 'data' => $data);
        } else {
            $flag = asset('public/admin-assets/img/country/country_badges/');
        }
        return array('status' => 'Success', 'Message' => 'Somthing went wrong');
        // }
    }

    public function showCheckedInList()
    {
        $auth = auth('sanctum')->user()->id;
        $checkin = DB::table('check_ins')->where('user_id', $auth)->join('user_models', 'user_models.id', '=', 'check_ins.user_id')
            ->join('cities', 'check_ins.city', '=', 'cities.id')->join('countries', 'check_ins.country', '=', 'countries.id')
            ->select('check_ins.*', 'user_models.username', 'cities.city_name', 'cities.city_badges', 'countries.country_name')->get();
        foreach ($checkin as $cityKey => $value) {
            $startdate = Carbon::parse($value->start_date);
            $formattedDate = $startdate->format('d F Y');

            $endDate = Carbon::parse($value->end_date);
            $formattedDates = $endDate->format('d F Y');

            $checkin[$cityKey]->startDate = $formattedDate;
            $checkin[$cityKey]->endDate = $formattedDates;

            $cityImage = asset('public/admin-assets/img/city/' . $value->city_badges);
            $checkin[$cityKey]->cityImage = $cityImage;
        }
        return array('status' => 'Success', 'data' => $checkin);
    }

    public function checkOutEarly(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feedsId' => 'required',
            'endDate' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }
        $auth = auth('sanctum')->user()->id;
        $checkIns = CheckIn::where(['id' => $request->feedsId, 'user_id' => $auth])->update(['end_date' => $request->endDate, 'is_checkout_trip' => '1']);
        return array('status' => 'Success', 'message' => 'Check-out successfully');
    }

    public function countryList(Request $request)
    {
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $countrys = Country::orderBy('id', 'desc')->take($per_page_limit)->skip($offset)->get();
        foreach ($countrys as $key => $country) {
            $countrys[$key]['country_url'] = asset('public/admin-assets/img/country/country_badges/' . $country->badges);
        }
        return array('status' => 'Success', 'data' => $countrys);
    }

    public function perticulerCountry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $country = Country::where('id', $request->country_id)->first();
        $checkInUsers = CheckIn::where('country', $country->country_name)->count();
        $country['checked_In_people'] = $checkInUsers;
        $country['country_path'] = asset('public/admin-assets/img/country/country_badges/' . $country->badges);
        if ($country) {
            return array('status' => 'Success', 'data' => $country);
        } else {
            return array('status' => 'Failed', 'data' => $country);
        }
    }

    public function cityList(Request $request)
    {
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;
        $citys = City::orderBy('id', 'desc')->take($per_page_limit)->skip($offset)->get();
        foreach ($citys as $key => $city) {
            $citys[$key]['city_url'] = asset('public/admin-assets/img/city/' . $city->city_badges);
        }
        return array('status' => 'Success', 'data' => $citys);
    }

    public function perticulerCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $citys = City::where('id', $request->city_id)->first();
        $checkInUsers = CheckIn::where('city', $citys->city_name)->count();
        $citys['total_checked_In_people'] = $checkInUsers;
        $citys['city_img_url'] = asset('public/admin-assets/img/city/' . $citys->city_badges);
        return array('status' => 'Success', 'data' => $citys);
    }

    public function userFollow(Request $request)
    {
        // dd(auth('sanctum')->user()->id);
        $validator = Validator::make($request->all(), [
            'following_id' => 'required|exists:user_models,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $auth = auth('sanctum')->user()->id;
        if ($request->following_id != $auth) {

            $checkPrivate = UserModel::where('id', $request->following_id)->first();         // is_private me 0 is public ac and 1 is private ac
            $exists = Follow::where(['user_id' => $auth, 'following_id' => $request->following_id]);
            $checkData = $exists->first();
            $follows = new Follow();
            $follows->user_id = $auth;
            $follows->following_id = $request->following_id;

            if ($checkPrivate->is_private == 0 || $checkData) {
                if ($checkData == null) {
                    $follows->status = (int)'1';
                    $follows->accept = (int)'1';
                    $follows->save();
                    return array('status' => 'Success', 'followStatus' => $follows->status, 'message' => 'You are now following new user');
                } else {
                    $unfollow = $exists->delete();
                    return array('status' => 'Success', 'followStatus' => null, 'message' => 'Unfollow successfully');
                }
            } else {
                $follows->status = (int)'0';
                $follows->accept = (int)'0';
                $follows->save();
                return array('status' => 'Success', 'followStatus' => $follows->status, 'message' => 'Requested successfully');
            }
        } else {
            return array('status' => 'Failed', 'message' => 'User cannot follow himself');
        }
    }

    public function acceptRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $auth = auth('sanctum')->user()->id;
        $checkUsers = Follow::where(['user_id' => $auth, 'following_id' => $request->request_id])->update(['status' => 1, 'accept' => 1]);
        if ($checkUsers) {
            return response()->json(['status' => 'success', 'message' => 'Request accepted successfully']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Request not accepted']);
        }
    }

    public function userFollowing(Request $request)
    {
         
        $validator = Validator::make($request->all(), [
            'per_page' => 'required',
            'page_no' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $search = $request->data;
        $auth = auth('sanctum')->user()->id;
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        // Fetch IDs of users being followed by the authenticated user
        $followingUserIds = Follow::where('user_id', $auth)
        ->where('status',1)
        ->where('accept',1)
        ->pluck('following_id'); 
        // Start building the query to fetch followings
        $query = UserModel::whereIn('id', $followingUserIds)
            ->select('id', 'full_name', 'username', 'pro_img', 'check_in_points', 'is_private', 'country', 'city', 'subscription_verified')
            ->orderBy('id', 'desc');

        // Add search conditions if search term is provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $followings = $query->take($per_page_limit)->skip($offset)->get();
        foreach ($followings as $key => $following) {
            if ($following->pro_img) {
                $img = asset('public/admin-assets/img/users/' . $following->pro_img);
                $followings[$key]['pro_img'] = $img;
            } else {
                $followings[$key]['pro_img'] = null;
            }

            if ($following->subscription_verified == 'false') {
                $following->subscription_verified = false;
            } else {
                $following->subscription_verified = true;
            }
            $currentDate = Carbon::now()->format('Y-m-d');
            $checkedIn = CheckIn::where(['user_id' => $following->id, 'verified_location' => 1])->where('end_date', '>', $currentDate)->first();
            if ($checkedIn) {
                $followings[$key]['country'] = $checkedIn->country;
                $followings[$key]['city']    = $checkedIn->city;
            } else {
                $followings[$key]['country'] = "Not checkedIn";
                $followings[$key]['city']    = "Not checkedIn";
            }
        }

        return response()->json(['status' => 'Success', 'data' => $followings]);
    }

    public function userFollowers(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'per_page' => 'required',
            'page_no' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $search = $request->data;
        $auth = auth('sanctum')->user()->id;
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $followersUserIds = Follow::where('following_id', $auth)->pluck('user_id');
        $query = UserModel::whereIn('id', $followersUserIds)
            ->select('id', 'full_name', 'username', 'pro_img', 'is_private', 'check_in_points', 'country', 'city', 'subscription_verified')
            ->orderBy('id', 'desc');

        // Add search conditions if search term is provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }
        $followers = $query->take($per_page_limit)->skip($offset)->get();
        foreach ($followers as $key => $following) {
            if ($following->pro_img) {
                $img = asset('public/admin-assets/img/users/' . $following->pro_img);
                $followers[$key]['pro_img'] = $img;
            } else {
                $followers[$key]['pro_img'] = null;
            }

            if ($following->subscription_verified == 'false') {
                $following->subscription_verified = false;
            } else {
                $following->subscription_verified = true;
            }
            $currentDate = Carbon::now()->format('Y-m-d');
            $checkedIn = CheckIn::where(['user_id' => $following->id, 'verified_location' => 1])->where('end_date', '>', $currentDate)->first();
            if ($checkedIn) {
                $followers[$key]['country'] = $checkedIn->country;
                $followers[$key]['city']    = $checkedIn->city;
            } else {
                $followers[$key]['country'] = "Not checkedIn";
                $followers[$key]['city']    = "Not checkedIn";
            }

            $followingCheck = Follow::where('user_id', $auth)->pluck('following_id')->all();
            if (in_array($following->id, $followingCheck)) {
                $followers[$key]['is_following'] = 'true';
            } else {
                $followers[$key]['is_following'] = 'false';
            }
        }

        return array('status' => 'Success', 'data' => $followers);
    }

    public function followerRemove(Request $request)
    {
        // dd(auth('sanctum')->user()->id);
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:follows,user_id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }
        $auth = auth('sanctum')->user()->id;
        $remove = Follow::where(['following_id' => $auth, 'user_id' => $request->id])->delete();
        if ($remove) {
            return array('status' => 'Success', 'message' => 'Remove successfully');
        } else {
            return array('status' => 'Failed', 'message' => 'OOPS! somthing went wrong');
        }
    }

    public function otherFollowing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|',
            'per_page' => 'required',
            'page_no' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $search = $request->data;
        $auth = auth('sanctum')->user()->id;
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $others = Follow::where('user_id', $request->user_id)->pluck('following_id');
        $query = UserModel::whereIn('id', $others)->select('id', 'full_name', 'username', 'pro_img', 'is_private', 'check_in_points', 'country', 'city', 'subscription_verified')->orderBy('id', 'desc');

        // Add search conditions if search term is provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }
        $othersFollowings = $query->take($per_page_limit)->skip($offset)->get();
        foreach ($othersFollowings as $key => $following) {
            if ($following->pro_img) {
                $img = asset('public/admin-assets/img/users/' . $following->pro_img);
                $othersFollowings[$key]['pro_img'] = $img;
            } else {
                $othersFollowings[$key]['pro_img'] = null;
            }

            if ($following->subscription_verified == 'false') {
                $following->subscription_verified = false;
            } else {
                $following->subscription_verified = true;
            }
            $currentDate = Carbon::now()->format('Y-m-d');
            $checkedIn = CheckIn::where(['user_id' => $following->id, 'verified_location' => 1])->where('end_date', '>', $currentDate)->first();
            if ($checkedIn) {
                $othersFollowings[$key]['country'] = $checkedIn->country;
                $othersFollowings[$key]['city']    = $checkedIn->city;
            } else {
                $othersFollowings[$key]['country'] = "Not checkedIn";
                $othersFollowings[$key]['city']    = "Not checkedIn";
            }

            // $followingCheck = Follow::where('user_id', $auth)->pluck('following_id')->all();
            // if (in_array($following->id, $followingCheck)) {
            //     $othersFollowings[$key]['is_following'] = 'true';
            // } else {
            //     $othersFollowings[$key]['is_following'] = 'false';
            // }

            $isfollowing = Follow::where('user_id', $auth)->get();
            $followingCheck = $isfollowing->pluck('following_id')->toArray();
            if (in_array($following->id, $followingCheck)) {
                // If the meeting id is in the following ids, set isfollow to the status
                $isfollow = $isfollowing->where('following_id', $following->id)->first()->status;
                $othersFollowings[$key]['is_following'] = $isfollow;
            } else {
                // If not, set isfollow to null
                $othersFollowings[$key]['is_following'] = null;
            }
        }
        return array('status' => 'Success', 'data' => $othersFollowings);
    }

    public function otherFollowers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|',
            'per_page' => 'required',
            'page_no' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $search = $request->data;
        $auth = auth('sanctum')->user()->id;
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;

        $followerss = Follow::where('following_id', $request->user_id)->pluck('user_id');
        $query = UserModel::whereIn('id', $followerss)->select('id', 'full_name', 'username', 'pro_img', 'is_private', 'check_in_points', 'country', 'city', 'subscription_verified')->orderBy('id', 'desc');

        // Add search conditions if search term is provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }
        $othersFollowers = $query->take($per_page_limit)->skip($offset)->get();
        foreach ($othersFollowers as $key => $following) {
            if ($following->pro_img) {
                $img = asset('public/admin-assets/img/users/' . $following->pro_img);
                $othersFollowers[$key]['pro_img'] = $img;
            } else {
                $othersFollowers[$key]['pro_img'] = null;
            }

            if ($following->subscription_verified == 'false') {
                $following->subscription_verified = false;
            } else {
                $following->subscription_verified = true;
            }
            $currentDate = Carbon::now()->format('Y-m-d');
            $checkedIn = CheckIn::where(['user_id' => $following->id, 'verified_location' => 1])->where('end_date', '>', $currentDate)->first();
            if ($checkedIn) {
                $othersFollowers[$key]['country'] = $checkedIn->country;
                $othersFollowers[$key]['city']    = $checkedIn->city;
            } else {
                $othersFollowers[$key]['country'] = "Not checkedIn";
                $othersFollowers[$key]['city']    = "Not checkedIn";
            }

            // $followingCheck = Follow::where('user_id', $auth)->pluck('following_id')->all();
            // if (in_array($following->id, $followingCheck)) {
            //     $othersFollowers[$key]['is_following'] = 'true';
            // } else {
            //     $othersFollowers[$key]['is_following'] = 'false';
            // }

            $isfollowing = Follow::where('user_id', $auth)->get();
            $followingCheck = $isfollowing->pluck('following_id')->toArray();
            if (in_array($following->id, $followingCheck)) {
                // If the meeting id is in the following ids, set isfollow to the status
                $isfollow = $isfollowing->where('following_id', $following->id)->first()->status;
                $othersFollowers[$key]['is_following'] = $isfollow;
            } else {
                // If not, set isfollow to null
                $othersFollowers[$key]['is_following'] = null;
            }
        }
        return array('status' => 'Success', 'data' => $othersFollowers);
    }

    public function userSearch(Request $request)
    {
        $auth = auth('sanctum')->user()->id;
        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;
        $following = Follow::where('user_id', $auth)->pluck('following_id')->all();
        $data = $request->get('data');
        $drivers = UserModel::select('id', 'is_private', 'full_name', 'username', 'pro_img', 'country', 'city', 'check_in_points')->where('full_name', 'like', "%{$data}%")->orWhere('username', 'like', "%{$data}%")->take($per_page_limit)->skip($offset)->get();
        foreach ($drivers as $key => $driver) {
            if ($driver->pro_img) {
                $drivers[$key]['img_url'] = asset('public/admin-assets/img/users/' . $driver->pro_img);
            } else {
                $drivers[$key]['img_url'] = null;
            }

            if ($driver->check_in_points >= 100) {
                $stage = "1";
                $remaining = $driver->check_in_points - 1108.16;
            } elseif ($driver->check_in_points >= 101.16 && $driver->check_in_points <= 1108.16) {
                $stage = "2";
                $remaining = $driver->check_in_points - 2116.32;
            } elseif ($driver->check_in_points >= 1019.16 && $driver->check_in_points <= 2116.32) {
                $stage = "3";
                $remaining = $driver->check_in_points - 3124.48;
            } elseif ($driver->check_in_points >= 2117.32 && $driver->check_in_points <= 3124.48) {
                $stage = "4";
                $remaining = $driver->check_in_points - 4132.64;
            } elseif ($driver->check_in_points >= 3125.48 && $driver->check_in_points <= 4132.64) {
                $stage = "5";
                $remaining = $driver->check_in_points - 5140.80;
            } elseif ($driver->check_in_points >= 4133.64 && $driver->check_in_points <= 5140.80) {
                $stage = "6";
                $remaining = $driver->check_in_points - 6148.96;
            } elseif ($driver->check_in_points >= 5141.80 && $driver->check_in_points <= 6148.96) {
                $stage = "7";
                $remaining = $driver->check_in_points - 7157.12;
            } elseif ($driver->check_in_points >= 6149.96 && $driver->check_in_points <= 7157.12) {
                $stage = "8";
                $remaining = $driver->check_in_points - 7157.12;
            } else {
                $stage = "0";
            }
            $drivers[$key]['goloca_level'] = $stage;
            if (in_array($driver->id, $following)) {
                $drivers[$key]['is_following'] = "true";
            } else {
                $drivers[$key]['is_following'] = "false";
            }
        }
        return array('status' => 'Success', 'data' => $drivers);
    }

    public function userLikes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feed_id' => 'required|exists:feeds,id',
            'liked_user_id' => 'required|exists:user_models,id',
            'like_unlike' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $auth = auth('sanctum')->user()->id;
        if ($auth) {
            $checkUser = UserLike::where(['user_id' => $auth, 'feed_id' => $request->feed_id])->first();
            if ($checkUser && $request->like_unlike == '0') {
                $update = DB::table('user_likes')->where('user_id', $auth)->update(['like_unlike' => $request->like_unlike]);
                return array('status' => 'Success', 'message' => 'Unliked Successfully');
            } else if ($checkUser && $request->like_unlike == '1') {
                $update = DB::table('user_likes')->where('user_id', $auth)->update(['like_unlike' => $request->like_unlike]);
                return array('status' => 'Success', 'message' => 'Again liked Successfully');
            } else {
                $likes = new UserLike();
                $likes->user_id = $auth;
                $likes->feed_id = $request->feed_id;
                $likes->liked_user_id = $request->liked_user_id;
                $likes->like_unlike = $request->like_unlike;
                $likes->save();
                return array('status' => 'Success', 'message' => 'Liked Successfully');
            }
        } else {
            return array('status' => 'Failed', 'message' => 'User cannot like his picture');
        }
    }

    public function upperUserComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feed_id' => 'required|exists:feeds,id',
            'commented_user_id' => 'required|exists:user_models,id',
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $tags_id = explode(',', $request->tagId);

        $auth = auth('sanctum')->user()->id;
        $comment = new Comment();
        $comment->user_id = $auth;
        $comment->feed_id = $request->feed_id;
        $comment->commented_user_id = $request->commented_user_id;
        $comment->content = $request->content;
        $comment->tag_user_id   = json_encode($tags_id, JSON_FORCE_OBJECT);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/comment/', $filename);
            $comment->image = $filename;
        }
        $comment->Save();
        return array('status' => 'Success', 'message' => 'Comment successfully');
    }

    public function responseComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required|exists:feeds,id',
            'response_content' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $responseTag_id = explode(',', $request->response_tag_user_id);

        $auth = auth('sanctum')->user()->id;
        $response = new CommentResponse();
        $response->user_id = $auth;
        $response->comment_id = $request->comment_id;
        $response->response_content = $request->response_content;
        $response->response_tag_user_id   = json_encode($responseTag_id, JSON_FORCE_OBJECT);
        if ($request->hasFile('response_image')) {
            $file = $request->file('response_image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/comment/', $filename);
            $response->response_image = $filename;
        }
        $response->save();
        return array('status' => 'Success', 'message' => 'Responsed successfully');
    }

    public function searchCountry(Request $request)
    {
        $country = $request->get('country');
        $data = Country::select('id', 'country_name')->where('country_name', 'like', "%{$country}%")->get();
        return array('status' => 'Success', 'data' => $data);
    }

    public function searchCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'city' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }
        $city = $request->get('city');
        $citydata = City::select('id', 'city_name')->where('country_name', $request->country_id)->where('city_name', 'like', "%{$city}%")->get();
        return array('status' => 'Success', 'data' => $citydata);
    }

    public function checkInCountdown()
    {
        $auth = auth('sanctum')->user()->id;
        $currentDate = Carbon::now()->format('Y-m-d');

        $checkIns = DB::table('check_ins')->where(['user_id' => $auth, 'verified_location' => '0'])->whereDate('start_date', '>', $currentDate)->orderBy('id', 'asc')->first();
        // dd($checkIns);

        $PreviousCheckIn = CheckIn::where(['user_id' => $auth, 'verified_location' => '1', 'is_checkout_trip' => '0'])->whereDate('start_date', '<=', $currentDate)->first();
        // dd($PreviousCheckIn);
        if ($PreviousCheckIn) {
            $currentTrip = CheckInLocation::where('user_id', $auth)->orderBy('id', 'desc')->first();
            $currentStartEndDate = CheckIn::where(['user_id' => $auth, 'verified_location' => '1'])->whereDate('start_date', '<=', $currentDate)->select('id', 'start_date', 'end_date')->first();
            $currentTrip->userStartEndDates = $currentStartEndDate;
        } else {
            $currentTrip = null;
        }

        return array('status' => 'Success', 'data' => array($checkIns), 'Current-Trip' => array($currentTrip));
    }

    public function historyCheckIn(Request $request)
    {
        $auth = auth('sanctum')->user()->id;
        $currentDate = Carbon::now()->format('Y-m-d');

        $checkIns = CheckIn::where('user_id', $auth)->whereDate('end_date', '<', $currentDate)->with(['countryNames:id,country_name', 'cityNames:id,city_name,city_badges'])->orderBy('id', 'desc')->get();

        $verifiedCheckIn = [];
        $unVerifiedCheckIn = [];

        foreach ($checkIns as $key => $checkIn) {
            $checkIns[$key]['cityNames']['cityNames'] = asset('public/admin-assets/img/city/' . $checkIn->cityNames->city_badges);

            if ($checkIn->verified_location == 1) {
                $verifiedCheckIn[] = $checkIn;
            } else {
                $unVerifiedCheckIn[] = $checkIn;
            }
        }

        return array('status' => 'Success', 'Verified' => $verifiedCheckIn, 'UnVerified' => $unVerifiedCheckIn);
    }

    public function homePage()
    {
        $auth = auth('sanctum')->user()->id;
        $currentDate = Carbon::now()->format('Y-m-d');

        // already checked In details 
        $checkIns = CheckIn::where('user_id', $auth)->whereDate('end_date', '<', $currentDate)->with(['countryNames:id,country_name', 'cityNames:id,city_name,city_badges'])->orderBy('id', 'desc')->limit('6')->get();
        $checkIns->each(function ($checkIn) {
            $checkIn->cityNames->cityImages = asset('public/admin-assets/img/city/' . $checkIn->cityNames->city_badges);
        });

        // user details for leaderbord 
        $users = UserModel::where('check_in_points', '!=', '0')->orderBY('check_in_points', 'desc')->select('id', 'full_name', 'username', 'pro_img', 'check_in_points', 'subscription_verified')->limit('6')->get();
        $rank = 1;
        $users->each(function ($user) use (&$rank) {
            // dd($user->check_in_points); 
            $user->rank = $rank;
            if($user->pro_img){
                $user->img_url = asset('public/admin-assets/img/users/' . $user->pro_img);
            }else{
                $user->img_url = null;
            }
            $user->localevel = $this->calculateStage($user->check_in_points);
            $rank++;
        });

        // details of explore location 
        $checkCities =  CheckIn::select('city', DB::raw('COUNT(*) as total_check_ins'))->orderBy('total_check_ins', 'desc')->groupBy('city')->limit('6')->pluck('city')->toArray();
        $cities = City::whereIn('city_name', $checkCities)->with('countryName')->get();
        $cities->each(function ($city) {
            $city->cityImages = asset('public/admin-assets/img/city/' . $city->city_badges);
        });

        // detail of explore groups data 
        $groups = Group::orderBy('id', 'desc')->with(['groupCountryNames:id,country_name', 'groupCityNames:id,city_name,city_badges'])->limit('6')->get();
        $groups->each(function ($group) {

            $members = json_decode($group->members, true);
            $admins = json_decode($group->admins, true);
            $moderators = json_decode($group->moderators, true);
            $members = json_decode($group->members, true);
            $role = 'user';
            $is_join = false;
            $is_request = false;

            // dd(auth('sanctum')->user()->id);
            $requested_user = json_decode($group->requested_user, true);
            if ($requested_user) {
                if (in_array(auth('sanctum')->user()->id, $requested_user)) {
                    $is_request = true;
                }
            }


            if ($admins) {

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

            if ($moderators) {
                if (in_array(auth('sanctum')->user()->id, $moderators)) {
                    $role = 'moderator';
                    $is_join = true;
                }
            }

            if ($members) {
                if (in_array(auth('sanctum')->user()->id, $members)) {
                    $is_join = true;
                    $role = 'member';
                }
            }

            $group->is_request = $is_request;
            $group->is_join = $is_join;
            $group->role = $role;
            $group->members_count = count((array) $members);

            $group->groupImages = asset('public/admin-assets/img/group/' . $group->image);
        });

        // details of meet peoples data
        $meets = UserModel::where('id', '!=', $auth)->select('id', 'full_name', 'username', 'pro_img', 'check_in_points', 'subscription_verified')->orderBy('check_in_points', 'desc')->limit('4')->get();
        $meets->each(function ($meet) use ($currentDate, $auth) {
            if($meet->pro_img){
                $meet->img_urls = asset('public/admin-assets/img/users/' . $meet->pro_img);
            }else{
                $meet->img_urls = null;
            }
            $meet->localevel = $this->calculateStage($meet->check_in_points);
            $checkIns = CheckIn::where('user_id', $meet->id)->whereDate('start_date', '>', $currentDate)->orderBy('start_date', 'asc')->first();

            $isfollowing = Follow::where('user_id', $auth)->get();
            $followingIds = $isfollowing->pluck('following_id')->toArray();
            if (in_array($meet->id, $followingIds)) {
                // If the meeting id is in the following ids, set isfollow to the status
                $isfollow = $isfollowing->where('following_id', $meet->id)->first()->status;
                $meet->isfollow = $isfollow;
            } else {
                // If not, set isfollow to null
                $meet->isfollow = null;
            }

            if ($checkIns) {
                // Retrieve location details for the upcoming check-in
                $location = CheckInLocation::where(['check_country' => $checkIns->country, 'check_city' => $checkIns->city])->latest()->first();
                if ($location) {
                    $meet->country = $location->check_country;
                    $meet->city    = $location->check_city;
                } else {
                    // If location details not found, set to default
                    $meet->country = null;
                    $meet->city = null;
                }
            } else {
                // If no upcoming check-in found, set location to null
                $meet->country = null;
                $meet->city = null;
            }
        });

        return array('status' => 'Success', 'historyCheckedIn' => $checkIns, 'leaderbord' => $users, 'exploreLocation' => $cities, 'group' => $groups, 'meetPeople' => $meets);
    }

    public function seeAllLeader(Request $request)
    {
        $auth = auth('sanctum')->user()->id;
        $per_page_limit = $request->per_page;
        $page_no = $request->page_no;
        $offset = ($page_no - 1) * $per_page_limit;
        $country = $request->country;
        $city = $request->city;
        $gender = $request->gender;


        $users = UserModel::where('check_in_points', '!=', '0');

        if ($country != 'null' && $country != null) {
            $users->where('country', $country);
        }

        if ($city != 'null' && $city != null) {
            $users->where('city', $city);
        }

        if ($gender != 'null' && $gender != null) {
            $users->where('gender', $gender);
        }



        // user details for leaderbord  see all
        $users = $users->orderBY('check_in_points', 'desc')->select('id', 'country', 'city', 'gender', 'full_name', 'username', 'pro_img', 'check_in_points', 'subscription_verified')->take($per_page_limit)->skip($offset)->get();
        $rank = 1;
        $users->each(function ($user) use (&$rank) {
            $user->rank = $rank;
            $user->img_url = asset('public/admin-assets/img/users/' . $user->pro_img);
            $user->localevel = $this->calculateStage($user->check_in_points);
            $rank++;
        });
        // dd($users);
        return array('status' => 'Success', 'seeAllLeader' => $users);
    }

    public function seeAllLocation(Request $request)
    {
        $auth = auth('sanctum')->user()->id;
        $currentDate = Carbon::now()->format('Y-m-d');
        $per_page_limit = $request->per_page;
        $page_no = $request->page_no;
        $offset = ($page_no - 1) * $per_page_limit;

        // details of explore location 
        $checkCities =  CheckIn::select('city', DB::raw('COUNT(*) as total_check_ins'))->orderBy('total_check_ins', 'desc')->groupBy('city')->take($per_page_limit)->skip($offset)->pluck('city');
        $cities = City::whereIn('id', $checkCities)->with('countryName')->get();
        $cities->each(function ($city) {
            $city->cityImages = asset('public/admin-assets/img/city/' . $city->city_badges);
        });
        return array('status' => 'Success', 'seeAllLocation' => $cities);
    }
    public function seeAllGroups(Request $request)
    {
    }
    public function seeAllmeetPeople(Request $request)
    {
        $auth = auth('sanctum')->user()->id;
        $per_page_limit = $request->per_page;
        $page_no = $request->page_no;
        $offset = ($page_no - 1) * $per_page_limit;
        $country = $request->country;
        $city = $request->city;
        $gender = $request->gender;


        $users = UserModel::where('id', '!=', $auth);

        if ($country != 'null' && $country != null) {
            $users->where('country', $country);
        }

        if ($city != 'null' && $city != null) {
            $users->where('city', $city);
        }

        if ($gender != 'null' && $gender != null) {
            $users->where('gender', $gender);
        }
        // details of meet peoples data
        $meets = $users->select('id', 'country', 'city', 'gender', 'full_name', 'username', 'pro_img', 'check_in_points', 'subscription_verified')->orderBy('check_in_points', 'desc')->limit('4')->get();
        $meets->each(function ($meet) {
            $meet->img_urls = asset('public/admin-assets/img/users/' . $meet->pro_img);
            $meet->localevel = $this->calculateStage($meet->check_in_points);
            $location = CheckInLocation::where('user_id', $meet->id)->latest()->first();
            $meet->country = $location ? $location->check_country : 'Not checkedIn';
            $meet->city = $location ? $location->check_city : 'Not checkedIn';
        });
        return array('status' => 'Success', 'seeAllmeetPeople' => $meets);
    }

    public function selectedLocation(Request $request)
    {
        // dd(auth('sanctum')->user()->id);
        $validator = Validator::make($request->all(), [
            'data'     => 'required',
            'per_page' => 'required',
            'page_no'  => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()]);
        }

        $per_page_limit = $request->per_page;           // limit of per page
        $page_no = $request->page_no;                   // page number 
        $offset = ($page_no - 1) * $per_page_limit;
        $auth = auth('sanctum')->user()->id;

        $perticulerCountry = Country::where('country_name', $request->data)->first();
        $perticulerCitysss = City::where('city_name', $request->data)->with('countryName', function ($query) {
            $query->select('id', 'country_name');
        })->first();

        if ($perticulerCountry) {
            $perticulerCountry->city_name = null;
            $perticulerCountry->city_badges = null;
            $countryObject = array(
                "id" => $perticulerCountry->id,
                "country_name" => $perticulerCountry->country_name,
            );
            $perticulerCountry->country_name = $countryObject;
            $perticulerCountry->badges = asset('public/admin-assets/img/country/country_badges/' . $perticulerCountry->badges);
            $visitedPeople = CheckIn::where('country', $request->data)->where('verified_location', 1)->count();
            $perticulerCountry->totalVisitedPeople = $visitedPeople;
            $datas = $perticulerCountry;
        } elseif ($perticulerCitysss) {
            // dd($perticulerCity);
            $perticulerCitysss->city_badges = asset('public/admin-assets/img/city/' . $perticulerCitysss->city_badges);
            $visitedPeople = CheckIn::where('city', $request->city)->where('verified_location', 1)->count();
            $perticulerCitysss->totalVisitedPeople = $visitedPeople;
            $datas = $perticulerCitysss;
        } else {
            return array('status' => 'Failed', 'message' => 'Please enter valid data');
        }

        // details of explore location 
        $findCountryId = Country::where('country_name', $request->data)->first();
        if ($findCountryId) {
            $checkCities =  CheckIn::where('country', $request->data)->select('city', DB::raw('COUNT(*) as total_check_ins'))->orderBy('total_check_ins', 'desc')->groupBy('city')->limit('6')->pluck('city')->toArray();
            if ($checkCities) {
                $cities = City::whereIn('city_name', $checkCities)->with('countryName')->get();
                $cities->each(function ($city) {
                    $city->cityImages = asset('public/admin-assets/img/city/' . $city->city_badges);
                });
                $exploreLocation = $cities;
            } else {
                $randomeCity = City::where('country_name', $findCountryId->id)->inRandomOrder()->with('countryName')->limit('6')->get();
                $randomeCity->each(function ($city) {
                    $city->cityImages = asset('public/admin-assets/img/city/' . $city->city_badges);
                });
                if ($randomeCity) {
                    $exploreLocation = $randomeCity;
                } else {
                    $exploreLocation = [];
                }
            }
        } else {
            $exploreLocation = [];
        }

        //================ private group with requested city =================
        $groups = Group::where('group_city', $request->data)->orderBy('id', 'desc')->take('6')->get();
        // dd($groups);
        $countryGroups = Group::where(['group_country' => $request->data, 'group_status' => 1])->orderBy('id', 'desc')->take('6')->get();

 

        
        if (count($groups)) {
            $groups->each(function ($group) {

                $members = json_decode($group->members, true);
                $transformedMembers = [];  
                $admins = json_decode($group->admins, true);
                $moderators = json_decode($group->moderators, true);
                $transformedadmins = [];
                $transformedmoderators = [];  
                $role = 'user'; 
                $requested_user = json_decode($group->requested_user, true);
        
                $is_join = false;
                $is_request = false;

                if($requested_user){
                    if(in_array(auth('sanctum')->user()->id, $requested_user)){
                        $is_request = true;
                    }  
                }
                if($moderators){
                    $moderators_count = count($moderators);
                    if(in_array(auth('sanctum')->user()->id, $moderators)){
                        $role = 'moderator';
                        $is_join = true;
                    } 
                }else{
                    $moderators_count = 0;
                }

                if($admins){
                    $admins_count = count($admins); 
                    if(count($admins) == 2){
                        if(auth('sanctum')->user()->id == $admins[0]){
                            $role = 'creater_admin';
                            $is_join = true;
                        }
                        if(auth('sanctum')->user()->id == $admins[1]){
                            $role = 'sub_admin';
                            $is_join = true;
                        }
                    }
                    else{
                        if(in_array(auth('sanctum')->user()->id, $admins)){
                            $role = 'creater_admin';
                            $is_join = true;
                        } 
                    }
                } 

                if($members){
                    $members_count = count($members); 
                    if(in_array(auth('sanctum')->user()->id, $members)){
                        $is_join = true;
                        $role = 'member';
                    }
                }else{
                    $members_count = 0;
                }
                $group->is_join = $is_join;
                $group->is_request = $is_request;
                $group->members_count = ($moderators_count + $members_count + $admins_count); 
                $group->image = asset('public/admin-assets/img/group/' . $group->image); 
 

            });
            $groupsData = $groups;
        } else {
            foreach ($countryGroups as $conkey => $conGroups) {



                $members = json_decode($conGroups->members, true);
                $transformedMembers = [];  
                $admins = json_decode($conGroups->admins, true);
                $moderators = json_decode($conGroups->moderators, true);
                $transformedadmins = [];
                $transformedmoderators = [];  
                $role = 'user'; 
                $requested_user = json_decode($conGroups->requested_user, true);
        
                $is_join = false;
                $is_request = false;

                if($requested_user){
                    if(in_array(auth('sanctum')->user()->id, $requested_user)){
                        $is_request = true;
                    }  
                }
                if($moderators){
                    $moderators_count = count($moderators);
                    if(in_array(auth('sanctum')->user()->id, $moderators)){
                        $role = 'moderator';
                        $is_join = true;
                    } 
                }else{
                    $moderators_count = 0;
                }

                if($admins){
                    $admins_count = count($admins);
                    if(count($admins) == 2){
                        if(auth('sanctum')->user()->id == $admins[0]){
                            $role = 'creater_admin';
                            $is_join = true;
                        }
                        if(auth('sanctum')->user()->id == $admins[1]){
                            $role = 'sub_admin';
                            $is_join = true;
                        }
                    }
                    else{
                        if(in_array(auth('sanctum')->user()->id, $admins)){
                            $role = 'creater_admin';
                            $is_join = true;
                        } 
                    }
                }

                if($members){
                    $members_count = count($members); 
                    if(in_array(auth('sanctum')->user()->id, $members)){
                        $is_join = true;
                        $role = 'member';
                    }
                }else{
                    $members_count = 0;
                }
                $conGroups->is_join = $is_join; 
                $conGroups->is_request = $is_request; 
                $conGroups->members_count = ($moderators_count + $members_count + $admins_count); 


                $conGroups->image   = asset('public/admin-assets/img/group/' . $conGroups->image); 


            }
            $groupsData = $countryGroups;
        }

        //================ meet people with requested city ===================
        $currentDate = Carbon::now()->format('Y-m-d');
        $meetPeoples = CheckIn::where('city', $request->data)->whereDate('start_date', '<=', $currentDate)->select('user_id')->pluck('user_id')->take('4')->all();
        $meetCountryPeople = CheckIn::where('country', $request->data)->whereDate('start_date', '<=', $currentDate)->select('user_id')->pluck('user_id')->take('4')->all();
        if ($meetPeoples) {
            $users = UserModel::whereNotIn('id', [auth('sanctum')->user()->id])
            ->whereIn('id', $meetCountryPeople) 
            ->select('id', 'gender', 'full_name', 'username', 'pro_img', 'check_in_points', 'subscription_verified')
            ->get();
            $users->each(function ($user) use ($currentDate) {

                $follow = Follow::where('user_id',auth('sanctum')->user()->id)->where('following_id',$user->id)->where('status', 1)->get()->toArray();
                if(!$follow){
                 $user->is_follow = false;
                }else{
                 $user->is_follow = true;
                } 

                $req = Follow::where('user_id',auth('sanctum')->user()->id)->where('following_id',$user->id)->where('status', 0)->get()->toArray();
                if(!$req){
                 $user->is_requested = false;
                }else{
                 $user->is_requested = true;
                } 

                $user->localevel = $this->calculateStage($user->check_in_points);
                if ($user->pro_img) {
                    $user->pro_img = asset('public/admin-assets/img/users/' . $user->pro_img);
                } else {
                    $user->pro_img = null;
                }

                if ($user->subscription_verified == 'true') {
                    $user->subscription_verified = true;
                } else {
                    $user->subscription_verified = false;
                }

                $checkedIn = CheckIn::where(['user_id' => $user->id, 'verified_location' => 1])->where(function ($query) use ($currentDate) {
                    $query->where('start_date', '>=', $currentDate)->orWhere('end_date', '>=', $currentDate);
                })->first();
                if ($checkedIn) {
                    $user['country'] = $checkedIn->country;
                    $user['city']    = $checkedIn->city;
                } else {
                    $user['country'] = "Not checkedIn";
                    $user['city']    = "Not checkedIn";
                }
            });
        } else {
            // 
            $users = UserModel::whereNotIn('id', [auth('sanctum')->user()->id])
            ->whereIn('id', $meetCountryPeople) 
            ->select('id', 'gender', 'full_name', 'username', 'pro_img', 'check_in_points', 'subscription_verified')
            ->get();
            $users->each(function ($user) use ($currentDate) {
                $follow = Follow::where('user_id',auth('sanctum')->user()->id)->where('following_id',$user->id)->where('status', 1)->get()->toArray();

                if(!$follow){
                 $user->is_follow = false;
                }else{
                 $user->is_follow = true;
                } 
                $req = Follow::where('user_id',auth('sanctum')->user()->id)->where('following_id',$user->id)->where('status', 0)->get()->toArray();
                if(!$req){
                 $user->is_requested = false;
                }else{
                 $user->is_requested = true;
                } 

                $user->localevel = $this->calculateStage($user->check_in_points);
                if ($user->pro_img) {
                    $user->pro_img = asset('public/admin-assets/img/users/' . $user->pro_img);
                } else {
                    $user->pro_img = null;
                }

                if ($user->subscription_verified == 'true') {
                    $user->subscription_verified = true;
                } else {
                    $user->subscription_verified = false;
                }

                $checkedIn = CheckIn::where(['user_id' => $user->id, 'verified_location' => 1])->where(function ($query) use ($currentDate) {
                    $query->where('start_date', '>=', $currentDate)->orWhere('end_date', '>=', $currentDate);
                })->first();
                if ($checkedIn) {
                    $user['country'] = $checkedIn->country;
                    $user['city']    = $checkedIn->city;
                } else {
                    $user['country'] = "Not checkedIn";
                    $user['city']    = "Not checkedIn";
                }
            });
        }

        //================= users feeds =============================
        $feeds = Feed::where('city', $request->data)
            ->orderBy('id', 'desc')
            ->with('userDetails:id,full_name,username,pro_img,check_in_points,subscription_verified')
            ->take($per_page_limit)
            ->skip($offset)
            ->get()
            ->makeHidden(['image', 'user_taging']);

        if ($feeds->isEmpty()) {
            $feeds = Feed::where('country', $request->data)
                ->orderBy('id', 'desc')
                ->with('userDetails:id,full_name,username,pro_img,check_in_points,subscription_verified')
                ->take($per_page_limit)
                ->skip($offset)
                ->get()
                ->makeHidden(['image', 'user_taging']);
        }

        foreach ($feeds as $key => $feed) {
            // dd($feeds[$key]['userDetails']['pro_img']);
            if ($feed->userDetails->pro_img != null) {
                $feeds[$key]['userDetails']['profile']  = asset('public/admin-assets/img/users/' . $feed->userDetails->pro_img);
            } else {
                $feeds[$key]['userDetails']['profile']  = null;
            }

            if ($feed->userDetails->subscription_verified == 'true') {
                $feeds[$key]['userDetails']['subscription_verified'] = true;
            } else {
                $feeds[$key]['userDetails']['subscription_verified'] = false;
            }

            // Calculate total likes and comments
            $feeds[$key]['totalLikes'] = UserLike::where('feed_id', $feed->id)->where('like_unlike', 1)->count();
            $feeds[$key]['totalComments'] = Comment::where('feed_id', $feed->id)->count();

            // Check if the authenticated user has liked the feed
            $isLiked = UserLike::where(['user_id' => $auth, 'feed_id' => $feed->id])->exists();
            $feeds[$key]['is_liked'] = $isLiked;
        }

        return response()->json(['status' => 'Success', 'datas' => $datas, 'exploreLocation' => $exploreLocation, 'groups' => $groupsData, 'meetpeoples' => $users, 'Feeds' => $feeds]);
    }
}
