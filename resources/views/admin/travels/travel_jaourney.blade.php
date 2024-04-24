@extends ('admin/index')
@section('content')


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>All Journey</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Journey</li>
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
                            <!-- <button type="button" class="btn btn-success float-right" data-toggle="modal"
                                data-target="#modal-default">Add Journey</button> -->
                        </div>
                        <!-- /.card-header -->
                        {{-- dd($users_list) --}}
                        <div class="card-body">
                            <table id="all-users-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>User Name</th>
                                        <th>Country</th>
                                        <th>City</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($travelJour as $travel)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $travel['user']['username'] }}</td>
                                            <td>
                                            <img src="{{asset('public/admin-assets/img/country/country_badges/' . $travel['countryNames']['badges'])}}" alt="img" width="100">.<br>
                                            {{ $travel->country }}</td>
                                            <td>
                                            <img src="{{asset('public/admin-assets/img/city/' . $travel['cityNames']['city_badges'])}}" alt="img" width="100">.<br>
                                            {{ $travel->city }}</td>
                                            <td>{{ $travel->start_date }}</td>
                                            <td>{{ $travel->end_date }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Id</th>
                                        <th>User Name</th>
                                        <th>Country</th>
                                        <th>City</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
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
                    <h4 class="modal-title">Add Journey</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="add_new_user_form" name="add_new_user_form" action="#"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">

                            <div class="form-group">
                                <label for="Inputfirst_name">Full name</label>
                                <input type="text" name="full_name" class="form-control full_name"
                                    placeholder="Enter First Name">
                            </div>

                            <div class="form-group">
                                <label for="">Country</label>
                                <input type="text" name="country" class="form-control country" placeholder="Enter country">
                            </div>

                            <div class="form-group">
                                <label for="">City</label>
                                <input type="text" name="city" placeholder="Enter city"
                                    class="form-control city">
                            </div>

                            <div class="form-group">
                                <label for=""> Start Date </label>
                                <input type="date" name="start_date" class="form-control start_date" placeholder="Enter start date">
                            </div>

                            <div class="form-group">
                                <label for=""> End Date </label>
                                <input type="date" name="end_date" class="form-control end_date" placeholder="Enter end date">
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
