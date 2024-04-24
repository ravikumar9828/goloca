@extends('admin/index')
@section('content')

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Add Blog</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">All Blogs</li>
                </ol>
            </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->
{{-- dd($blogs_list) --}}


<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">  
                    <div class="card-header">
                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-default">Add Blog</button>
                    </div>             
                    <div class="card-body">
                        <table id="allblogslisting" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Image</th>               
                                    <th>Title</th>
                                    <th>Content</th>
                                    <th>Status</th>                
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @php
                                    $count = 1;
                                @endphp                    
                                @foreach($blogs_list as $blog)
                                    <tr>
                                        <td>{{$count++}}</td>
                                        <td><img src="{{URL::asset('/admin-assets/img/blog_img/')}}/{{$blog->image}}" alt="" height="60" width="60"></td>
                                        <td>{{$blog->title}}</td>  
                                        <!-- <td>{!! $blog->content !!}</td>  -->
                                        <td>{{strip_tags(preg_replace("/&#?[a-z0-9]+;/i","",$blog->content) )}}</td>   
                                        <td>{{$blog->status}}</td> 
                                        <td>
                                            <a href="" class="edit_blog admin_edit_blog" blog_id="{{$blog->id}}" blog_title="{{$blog->title}}" blog_content="{{$blog->content}}" blog_icon_path="{{asset('/admin-assets/img/blog_img')}}" blog_icon="{{$blog->image}}" blog_status="{{$blog->status}}">Edit</a>
                                            /
                                            <form method="POST" action="{{ route('deleteblog', $blog->id) }}">
                                                @csrf
                                                <input name="_method" type="hidden" value="DELETE">
                                                <a href="" class="delete_blog admin_delete_blog show_confirm">Delete</a>
                                            </form>
                                        </td>                  
                                    </tr>                                
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>S.no</th>
                                    <th>Image</th>               
                                    <th>Title</th>
                                    <th>Content</th>
                                    <th>Status</th>                
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>

<div class="modal modal_blog fade" id="modal-default">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add New Blog</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
              
                <form id="add_new_blog_form" action="{{route('insertblog')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">      

                        <div class="form-group blog_img">
                            <div class="containers">      
                                <div class="imageWrapper">
                                    <img class="image show_cat_icon" src="">
                                </div>
                            </div>
                            <button class="file-upload">            
                                <input type="file" name="blog_icon" class="cat-file-input" value="" accept="image/png, image/gif, image/jpeg" >Choose File
                            </button>
                        </div>                      

                        <div class="form-group">
                            <label for="Inputtitle">Title</label>                                
                                <input type="text" name="title" class="form-control title" placeholder="Enter Title">  
                        </div>

                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea name="content" class="form-control content" id="content">  </textarea>
                        </div>  

                        <div class="form-group">
                            <div class="form-check">
                                <input type="radio" name="blog_status" id="blog_status_active" class="form-check-input blog_status_active" value="active" checked>
                                <label class="form-check-label" for="blog_status_active">Active</label>
                            </div>  
                            <div class="form-check">
                                <input type="radio" name="blog_status" id="blog_status_inactive" class="form-check-input blog_status_inactive" value="inactive">
                                <label class="form-check-label" for="blog_status_inactive">Inactive</label>
                            </div> 
                        </div>
                                                      

                    </div> 

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary blogsubmit">Submit</button>
                    </div>
                </form>
                

            </div>            
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


@endsection