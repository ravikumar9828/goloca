@extends ('admin/index')
@section('content')


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>All Hellps</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Helps</li>
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
                                        <th>Username</th>
                                        <th>Title</th>
                                        <th>Desciption</th>
                                        <th>Image</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($hepls as $hepl)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $hepl->username }}</td>
                                            <td>{{ $hepl->title }}</td>
                                            <td>{{ $hepl->desciption }}</td>
                                            <td><img src="{{ asset('public/admin-assets/img/help') }}/{{ $hepl->image }}"
                                                    width="100">
                                            </td>
                                            <td>
                                                @if ($hepl->Status == 'active')
                                                    <p class="text-success">Active</p>
                                                @else
                                                    <p class="text-danger">Inactive</p>
                                                @endif
                                            </td>
                                            <td>{{ $hepl->created_at }}</td>
                                            <td>
                                                <a href="#" class="btn btn-danger btn-sm">Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Id</th>
                                        <th>Username</th>
                                        <th>Title</th>
                                        <th>Desciption</th>
                                        <th>Image</th>
                                        <th>Status</th>
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
