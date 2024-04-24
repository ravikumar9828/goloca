@extends('admin/index')
@section('title','Edit Page')
@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit About-US</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item"><a href="#">All Pages</a></li>
          <li class="breadcrumb-item active">Edit About-US</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
<div class="container-fluid">
  <div class="row">

   {{-- dd($pagecontent) --}}

    <div class="card-body">
      <form role="form" method="post" action="{{url('admin/update-about-page')}}" enctype="multipart/form-data">
        @csrf

        <div class="form-group col-6">
            <label for="meta_title name">Title</label>
            <input type="text" class="form-control" name="title" placeholder="Enter Title" value="@if(isset($pagecontent->title)) {{ $pagecontent->title }} @endif">
                @if ($errors->has('title'))
                <div class="form-valid-error">
                    {{ $errors->first('title') }}
                </div>
            @enderror
        </div>

        <div class="col-6 row">
            <div class="form-group col-12">
            <label for="platform_blog_button_text">Description</label>
            <textarea  id="summernote" class="form-control textarea" rows="8" name="description" placeholder="Enter Description">@if(isset($pagecontent->description)) {{ $pagecontent->description }}  @endif</textarea>
            @if ($errors->has('description'))
                <div class="form-valid-error">
                    {{ $errors->first('description') }}
                </div>
                @enderror
            </div>
        </div>

        <div class="form-group col-6">
            <label for="meta_title name">Image</label>
            <input type="file" class="form-control" name="image" value="">
            <img src="@if(isset($pagecontent->image)) {{asset('public/admin-assets/img/about/'.$pagecontent->image)}} @endif" alt="image" width="100" class="mt-3">
                @if ($errors->has('image'))
                <div class="form-valid-error">
                    {{ $errors->first('image') }}
                </div>
            @enderror
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>

      </form>
    </div>

  </div>
  <!-- /.row -->
</div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<script>
  $(function () {
    // Summernote
    $('#summernote').summernote()

    // CodeMirror
    CodeMirror.fromTextArea(document.getElementById("codeMirrorDemo"), {
      mode: "htmlmixed",
      theme: "monokai"
    });
  })
</script>


@endsection
