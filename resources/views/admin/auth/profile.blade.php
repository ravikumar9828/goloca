@extends ('admin/index')
@section('content')

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Profile</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">User Profile</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">

                    <!-- Profile Image -->
                    <div class="card card-primary card-outline admin_edit_profile_image">
                        <div class="card-body box-profile">

                            <form method="post" action="{{ route('admin.uploadimage') }}" id="upload-image-form"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="avatar-upload">
                                    <div class="avatar-edit">
                                        <input type='file' name="image" id="adminimageUpload"
                                            accept=".png, .jpg, .jpeg" onchange="readURL(this);" />
                                        <label for="adminimageUpload"></label>
                                    </div>
                                    <div class="avatar-preview">
                                        <div id="imagePreview"
                                            style="background-image: url({{ asset('/public/admin-assets/assets/img/profile_img/admin/') }}/{{ $data->pro_img }});">
                                        </div>

                                    </div>
                                </div>
                            </form>
                            <h3 class="profile-username text-center">Rishi mancham</h3>

                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>

                </div>

                <!-- /.col -->
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#information"
                                        data-toggle="tab">Info</a></li>
                                <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a>
                                </li>
                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">

                                <!------Information Tab-------->
                                <div class="tab-pane active" id="information">
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">UserName :</label>
                                        <div class="col-sm-10 col-form-label">{{ $data->name }}</div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Email : </label>
                                        <div class="col-sm-10 col-form-label">{{ $data->email }}</div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Mobile : </label>
                                        <div class="col-sm-10 col-form-label">{{ $data->mobile }}</div>
                                    </div>
                                </div>

                                <!------Setting Tab-------->
                                <div class="tab-pane" id="settings">
                                    <form class="form-horizontal" method="post" action="{{ route('update_admin_info') }}"
                                        name="general_info" id="general_info">
                                        @csrf
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                                            <div class="col-sm-10">
                                                @error('username')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                                <input type="text"
                                                    class="form-control @error('username') is-invalid @enderror"
                                                    id="username" name="username" placeholder="User Name"
                                                    value="{{ $data->name }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                            <div class="col-sm-10">
                                                @error('email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror" id="email"
                                                    name="email" placeholder="Email" value="{{ $data->email }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-2 col-form-label">phone</label>
                                            <div class="col-sm-10">
                                                @error('mobile')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                                <input type="text"
                                                    class="form-control @error('mobile') is-invalid @enderror"
                                                    id="mobile" name="mobile" placeholder="Mobile"
                                                    value="{{ $data->mobile }}">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <button type="submit" class="btn btn-danger">Submit</button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-----Password---->
                                    <form class="form-horizontal" action="{{ route('update_admin_password') }}"
                                        method="post" name="password_info" id="password_info">
                                        @csrf

                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-2 col-form-label">Current
                                                Password</label>
                                            <div class="col-sm-10">
                                                @error('current_password')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                                <input type="password"
                                                    class="form-control @error('current_password') is-invalid @enderror"
                                                    id="current_password" name="current_password"
                                                    placeholder="Current Password" value="{{ old('current_password') }}">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-2 col-form-label">New Password</label>
                                            <div class="col-sm-10">
                                                @error('password')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                                <input type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    id="password" name="password" placeholder="New Password"
                                                    value="{{ old('password') }}">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-2 col-form-label">Confirm
                                                Password</label>
                                            <div class="col-sm-10">
                                                @error('confirm_password')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                                <input type="password"
                                                    class="form-control @error('confirm_password') is-invalid @enderror"
                                                    id="confirm_password" name="confirm_password"
                                                    placeholder="Confirm Password" value="{{ old('confirm_password') }}">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <button type="submit" class="btn btn-danger">Submit</button>
                                            </div>
                                        </div>

                                    </form>


                                </div>
                                <!-- /.tab-pane -->
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
                <!-- /.row -->
            </div><!-- /.container-fluid -->
    </section>



    <script type="text/javascript">
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').css('background-image', 'url(' + e.target.result + ')');
                    $('#imagePreview').hide();
                    $('#imagePreview').fadeIn(650);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }


        $('#adminimageUpload').change(function(e) {
            e.preventDefault();
            $('#upload-image-form').submit();
        });

        $('#upload-image-form').submit(function(e) {
            e.preventDefault();


            let formData = new FormData(this);
            // Append data
            //formData.append('file',files[0]);
            //formData.append('_token',CSRF_TOKEN);
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.uploadimage') }}",
                data: formData,
                //data : {},
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                //success: function(result){ alert(result); }
                success: (response) => {
                    if (response) {
                        this.reset();
                        //alert('Image has been updated successfully');
                        toastr.success('Image has been updated successfully');
                    }
                },
                error: function(response) {
                    console.log(response);
                    //$('#image-input-error').text(response.responseJSON.errors.file);
                }
            });
        });
    </script>


@stop
