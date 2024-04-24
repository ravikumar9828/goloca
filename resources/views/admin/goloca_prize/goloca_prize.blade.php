@extends ('admin/index')
@section('content')
@php
use Carbon\Carbon;
@endphp
<style>
    .modal-dialog {
    max-width: 900px;
    margin: 1.75rem auto;
}
</style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>All goloca prizes</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">DataTables</li>
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
                            <button type="button" class="btn btn-default float-right" data-toggle="modal"
                                data-target="#modal-default">Add goloca prize</button>
                        </div>
                        <!-- /.card-header -->
                        {{-- dd($users_list) --}}
                        <div class="card-body">
                            <table id="all-users-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Title</th>
                                        <th>Image</th>
                                        <th>description</th>
                                        <th>min goloca level</th>
                                        <th>min participants</th>
                                        <th>current participants</th>
                                        <th>start date</th>
                                        <th>end date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users_list as $user)
                                    <?php   
                                    
                                   $date_start_date = Carbon::createFromFormat('d M, Y', $user->start_date);
                                    $start_date = $date_start_date->toDateString();
                                    if ($user->end_date === null) {
                                        $end_date = null;
                                    }else{
                                   $date_end_date = Carbon::createFromFormat('d M, Y', $user->end_date);
                                    $end_date = $date_end_date->toDateString();
                                }
                                    ?>
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $user->title }}</td>
                                            
                                            <td>
                                                @if ($user->image == null)
                                                    <img src="{{ asset('public/admin-assets/img/users/default.png') }}"
                                                        alt="" width="100">
                                                @else
                                                    <img src="{{ asset('public/admin-assets/img/goloca_prize/' . $user->image) }}"
                                                        alt="" width="100">
                                                @endif
                                            </td>
                                            <td>{{ $user->description }}</td>
                                            <td>{{ $user->min_goloca_level }}</td>
                                            <td>{{ $user->min_participants }}</td>
                                            <td>{{ $user->total_participants ?? 0 }}</td>
                                            <td>{{ $user->start_date }}</td>
                                            <td>{{ $user->end_date ?? 'null' }}</td>
                                            <td>
                                                @if ($user->status == 'active')
                                                    <h5 class="text-success">Active</h5>
                                                @else
                                                    <h5 class="text-danger">Inactive</h5>
                                                @endif
                                            </td>
                                            <td class="edit_btns">
                                                <a href="" class="edit_user admin_edit_goloca_prize btn btn-success btn-sm"
                                                    user_id="{{ $user->id }}" title="{{ $user->title }}" description="{{ $user->description }}"
                                                    min_goloca_level="{{ $user->min_goloca_level }}" min_participants="{{ $user->min_participants }}" total_participants="{{ $user->total_participants }}" start_date="{{ $start_date }}" end_date="{{ $end_date }}" profile_image="{{ $user->image }}"
                                                    profilePath="{{ asset('public/admin-assets/img/goloca_prize') }}"
                                                    user_status="{{ $user->status }}">Edit</a>

                                                <form method="get" action="{{ route('admin.deletegoloca_prize', $user->id) }}">
                                                    @csrf
                                                    <input name="_method" type="hidden" value="DELETE">
                                                    <!-- <button type="submit" class="btn btn-xs btn-danger btn-flat show_confirm" data-toggle="tooltip" title='Delete'>Delete</button> -->
                                                    <a href="{{ route('admin.deletegoloca_prize', $user->id) }}"
                                                        class="delete_user admin_delete_users show_confirm btn btn-danger btn-sm">Delete</a>
                                                </form>

                                                <!-- <a href="{{ url('admin/show-gallery/' . $user->id) }}"
                                                    class="btn btn-primary btn-sm">show</a> -->
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                    <th>Id</th>
                                    
                                        <th>Title </th>
                                        <th>Image </th>
                                        <th>description </th>
                                        <th>min goloca level </th>
                                        <th>min participants </th>
                                        <th>current participants </th>
                                        <th>start date </th>
                                        <th>end date </th>
                                        <th>Status </th>
                                        <th>Action </th>
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

    <div class="modal fade" id="modal-default">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New goloca prize</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="add_goloca_prize_form" name="add_goloca_prize_form" action="{{ route('admin.insertgoloca_prize') }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">

                            <div class="form-group">
                                <label for="Inputfirst_name">Title</label>
                                <input type="text" name="title" class="form-control title"
                                    placeholder="Enter First title">
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="description" class="form-control description"
                                    placeholder="Enter description">
                            </div>

                            <div class="form-group">
                                <label for="">Min goloca level</label>
                                <input type="text" name="min_goloca_level" class="form-control min_goloca_level" placeholder="Enter min goloca level">
                            </div>

                            <div class="form-group">
                                <label for="">Min participants</label>
                                <input type="text" name="min_participants" placeholder="Enter min participants"
                                    class="form-control min_participants">
                            </div>
                            <!-- <div class="form-group">
                                <label for="">Total participants</label>
                                <input type="text" name="total_participants" placeholder="Enter total participants"
                                    class="form-control total_participants">
                            </div> -->
                            <div class="form-group">
                                <label for="">Start date</label>
                                <input type="date" name="start_date" placeholder="Enter start date"
                                    class="form-control start_date">
                            </div>
                            <div class="form-group">
                                <label for="">End date</label>
                                <input type="date" name="end_date" placeholder="Enter end date"
                                    class="form-control end_date">
                            </div>

                            <div class="form-group">
                                <label for="">Image</label>
                                <input type="file" name="pro_img" class="form-control pro_img" value="">
                                <img src="" class="pro_image mt-1" alt="" width="100">
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="radio" name="user_status" id="user_status_active"
                                        class="form-check-input user_status_active" value="active" checked>
                                    <label class="form-check-label" for="user_status_active">Active</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="user_status" id="user_status_inactive"
                                        class="form-check-input user_status_inactive" value="inactive">
                                    <label class="form-check-label" for="user_status_inactive">Inactive</label>
                                </div>
                            </div>

                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>


                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

@stop
