<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\BlogsModel;
use App\Traits\Common_trait;

class BlogsController extends Controller
{
    use Common_trait;
    
    public function all_blogs_list(){
        $blogs_list = BlogsModel::all();
        //dd($blogs_list);
        return view('admin.blogs.index', compact('blogs_list'));
    }

    public function insert_blog(Request $request){

        $formdata = $request->all();
        //dd($formdata); 

        //Create slug
        $blog_slug = $this->create_unique_slug($formdata['title'], 'blogs_models', 'slug');
        

        //////////////Store Image////////////////
        $file = $request->file('blog_icon');
        //Get uploaded file information
        if ($request->file('blog_icon')) {
            
            $originalname = $request->file('blog_icon')->getClientOriginalName();               
            $fileextension = $request->file('blog_icon')->extension(); 
            $blogfilename = $blog_slug.'.'.$fileextension;
            //$path = $request->file('blog_icon')->storeAs('profile_img', $originalname);

            //Create directory if not exist
            $path = public_path('admin-assets/img/blog_img');
            /*
            if(!File::isDirectory($path)){
                File::makeDirectory($path, 0777, true, true);
            }
            */
            //$destinationPath = public_path().'admin-assets/img/blog_img' ;
            $path = $request->file('blog_icon')->move($path,$blogfilename);
                  
        } 

        //////////////Insert Data////////////////
        $blogs = new BlogsModel; 
        $blogs->title = $formdata['title'];        
        $blogs->content = $formdata['content'];       
        $blogs->slug = $blog_slug;
        $blogs->image = $blogfilename;  
        $blogs->status = $formdata['blog_status'];                  
        $blogs->save();
        //Last inserted ID
        if($blogs->id > 0){
            return back()->with('flash-success', 'You Have Successfully Added New Blog.');
        } else{
            return back()->with('flash-error', 'something went wrong.');
        }         
       
    }

    public function edit_blog(Request $request){
        $formdata = $request->all();
        //dd($formdata); 
        
        $hidden_blog_id = $formdata['hidden_blog_id'];
        $get_blog = BlogsModel::find($hidden_blog_id);
        //dd($get_blog);  

        /////////Check if title is changed then change the slug also///////////
        if($get_blog->title != $formdata['title']){
            $blog_slug = $this->create_unique_slug($formdata['title'], 'blogs_models', 'slug');            
        } else {
            $blog_slug = $get_blog->slug;             
        }  

        //////////////Store Image////////////////
        $file = $request->file('blog_icon');
        //Get uploaded file information
        if ($request->file('blog_icon')) {            
            
            $originalname = $request->file('blog_icon')->getClientOriginalName();               
            $fileextension = $request->file('blog_icon')->extension(); 
            $Newfilename = $blog_slug.'.'.$fileextension;
            //$path = $request->file('blog_icon')->storeAs('profile_img', $originalname);

            //Create directory if not exist
            $path = public_path('admin-assets/img/blog_img');
            /*
            if(!File::isDirectory($path)){
                File::makeDirectory($path, 0777, true, true);
            }
            */
            if(file_exists($path.'/'.$get_blog->image)){ 
                unlink($path.'/'.$get_blog->image);
            }
            
            $destinationPath = public_path().'admin-assets/img/blog_img' ;
            $path = $request->file('blog_icon')->move($path,$Newfilename);
            //Create Custom File Name
            $edited_filename = $blog_slug.'.'.$fileextension;
                  
        } else {

            ////Rename Image Name according to New Title or Slug
            $img = $get_blog->image;
            $get_extension = explode(".", $img); 
            $dir = public_path('admin-assets/img/blog_img');
            $oldfile_name = $dir.'/'.$get_blog->image;
            $newfile_name = $dir.'/'.$blog_slug.'.'.$get_extension[1];
            rename($oldfile_name, $newfile_name);
            //Create Custom File Name
            $edited_filename = $blog_slug.'.'.$get_extension[1];

        } 

        //////////////Update Data////////////////        
        // $get_blog = ParentCategoryModel::find($hidden_cat_id);
        $get_blog->title = $formdata['title'];        
        $get_blog->content = $formdata['content'];       
        $get_blog->slug = $blog_slug;
        $get_blog->image = $edited_filename;    
        $get_blog->status = $formdata['blog_status'];                  
        $get_blog->save();
        //Last inserted ID
        if($get_blog->id > 0){
            return back()->with('flash-success', 'You Have Successfully Edit Blog.');
        } else{
            return back()->with('flash-error', 'something went wrong.');
        }

    }

    public function delete_blog($id){

        $data = BlogsModel::find($id)->delete();
        if($data){   
            return back()->with('flash-success', 'You Have Successfully Deleted Blog.');
        } else{
            return back()->with('flash-error', 'something went wrong.');
        }

    }

}
