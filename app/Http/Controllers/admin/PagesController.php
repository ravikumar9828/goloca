<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\Feed;
use App\Models\Gallery;
use App\Models\Help;
use App\Models\UserModel;
use App\Models\CheckIn;
use Auth;
use DB;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function editprivacy()
    {
        $pagecontent = About::orderBy('id', 'desc')->first();
        return view('admin/pages/edit-about-us', compact('pagecontent'));
    }

    // public function updatepages(Request $request){
    //     $formdata = $request->all();
    //     dd($formdata);
    //     $pagecontent = PagesModel::where('page_name', $request->title)->get();
    //     // return view('admin/pages/edit-content', compact('pagecontent', 'pagename'));
    // }

    public function aboutUs(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'image' => 'required',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin-assets/img/about', $filename);
        }

        $updateOrCreates = DB::table('abouts')->updateOrInsert(
            ['id' => 1],
            [
                'title' => $request->title,
                'description' => $request->description,
                'image' => $filename,
            ]
        );
        return back()->with('flash-success', 'You have update add about-us.');
    }

    public function showHelp()
    {
        $hepls = DB::table('helps')->orderBy('id', 'desc')->join('user_models', 'user_models.id', '=', 'helps.user_id')->select('helps.*', 'user_models.username')->get();
        return view('admin.pages.help',compact('hepls'));
    }

    public function userFeeds()
    {
        $feeds = DB::table('feeds')->orderBy('id', 'desc')->join('user_models', 'user_models.id', '=', 'feeds.id')->select('feeds.*', 'user_models.username')->get();
        return view('admin.pages.user_feeds', compact('feeds'));
    }

    public function deleteFeeds($id)
    {
        $feeds = Feed::find($id)->delete();
        if ($feeds) {
            return back()->with('flash-success', 'You have successfully deleted feeds.');
        } else {
            return back()->with('flash-error', 'something went wrong.');
        }
    }

    public function gallery($id)
    {
        $users = UserModel::where('id', $id)->orderBy('id', 'desc')->first();
        $gallerys = Gallery::where('user_id', $users->id)->get();
        return view('admin.users.gallery', compact('gallerys'));
    }

    public function seeCheckIn()
    {
        $travelJour = CheckIn::orderBy('id', 'desc')->with(['user:id,username', 'countryNames:country_name,badges', 'cityNames:city_name,city_badges'])->get();
        return view('admin.travels.travel_jaourney', compact('travelJour'));
    }
}
