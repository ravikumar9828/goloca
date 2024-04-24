@extends ('admin/index')
@section('content')


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>All Users Feeds</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Users Feeds</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            {{-- <button type="button" class="btn btn-default float-right" data-toggle="modal"
                                data-target="#modal-default">Add Users Feeds</button> --}}
                        </div>
                        <!-- /.card-header -->
                        {{-- dd($users_list) --}}
                        <div class="card-body">
                            <table id="all-users-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>User Name</th>
                                        <th>Desciption</th>
                                        <th>Image/Video</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($feeds as $feed)
                                    <?php $singles = json_decode($feed->image, true); ?>
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $feed->username }}</td>
                                            <td>{{ $feed->description }}</td>
                                            <td style="width: 280px;">
                                            @foreach ($singles as $single)
                                            <?php $path = pathinfo($single, PATHINFO_EXTENSION); ?>
                                            @if($path == 'mp4' || $path == 'avi' || $path == 'MOV' || $path == 'mpeg' || $path == 'mkv' || $path == 'gif')
                                            <video src="{{ asset('public/admin-assets/img/user_feeds') }}/{{ $single }}" width="100px" controls></video>
                                            @elseif($path == 'jpg' || $path == 'jpeg' || $path == 'png' || $path == 'bmp' || $path == 'tif')
                                            <img src="{{ asset('public/admin-assets/img/user_feeds') }}/{{ $single }}" width="100px">
                                            @endif
                                            @endforeach
                                            </td>
                                            <td>{{ $feed->created_at }}</td>
                                            <td>
                                                <a href="{{ url('admin/delete-feeds/'.$feed->id) }}"
                                                    class="btn btn-danger btn-sm">Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Id</th>
                                        <th>User Name</th>
                                        <th>desciption</th>
                                        <th>Status</th>
                                        <th>image</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>

@stop
