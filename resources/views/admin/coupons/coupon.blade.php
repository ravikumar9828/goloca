@extends ('admin/index')
@section('content')


    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>All Coupon Code</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Coupon Code</li>
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
                                data-target="#modal-coupon">Coupon Code</button>
                        </div>
                        <!-- /.card-header -->
                        {{-- dd($users_list) --}}
                        <div class="card-body">
                            <table id="all-users-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Flat</th>
                                        <th>value</th>
                                        <th>Quantity</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Coupon</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($coupons as $coupon)
                                        {{-- @dd($coupon->status) --}}
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $coupon->type }}</td>
                                            <td>{{ $coupon->value }}</td>
                                            <td>{{ $coupon->quantity }}</td>
                                            <td>{{ $coupon->start }}</td>
                                            <td>{{ $coupon->end }}</td>
                                            <td>{{ $coupon->coupon }}</td>
                                            <td>
                                                @if ($coupon->status == 'active')
                                                    <h5 class="text-success">Active</h5>
                                                @else
                                                    <h5 class="text-danger">Inactive</h5>
                                                @endif
                                            </td>
                                            <td>
                                                <a href=""
                                                    class="edit_parentcat admin_edit_coupon btn btn-success btn-sm"
                                                    coupon_id="{{ $coupon->id }}" coupon_type="{{ $coupon->type }}"
                                                    coupon_value="{{ $coupon->value }}" coupon_quantity="{{ $coupon->quantity }}"
                                                    coupon_start="{{ $coupon->start }}" coupon_end="{{ $coupon->end }}"
                                                    coupon_coupon="{{ $coupon->coupon }}"  coupon_status="{{ $coupon->status }}">Edit
                                                </a>

                                                <a href="{{url('admin/delete-coupon/'.$coupon->id)}}" class="btn btn-danger btn-sm">Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Id</th>
                                        <th>Flat</th>
                                        <th>value</th>
                                        <th>Quantity</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Coupon</th>
                                        <th>Status</th>
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

    <div class="modal fade" id="modal-coupon">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New User</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="add_coupon_form" name="add_coupon_form" action="{{ route('createCoupon') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">

                            <div class="form-group">
                                <label for="Inputfirst_name">Type</label>
                                <select name="type" id="type" class="type form-control">
                                    <option value="">Select type</option>
                                    <option value="upto">UPTO</option>
                                    <option value="flat">FLAT</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="">Value</label>
                                <input type="text" name="value" class="form-control value" placeholder="Enter value">
                            </div>

                            <div class="form-group">
                                <label for="">Quantity</label>
                                <input type="text" name="quantity" class="form-control quantity"
                                    placeholder="Enter quantity">
                            </div>

                            <div class="form-group">
                                <label for="">Start Date</label>
                                <input type="date" name="start" class="form-control start">
                            </div>

                            <div class="form-group">
                                <label for="">End Date</label>
                                <input type="date" name="end" class="form-control end">
                            </div>

                            <div class="form-group">
                                <label for="">Coupon</label>
                                <input type="text" name="coupon" class="form-control coupon" placeholder="Enter Coupon">
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="radio" name="status" id="user_status_active"
                                        class="form-check-input coupon_status_active" value="active" checked>
                                    <label class="form-check-label" for="user_status_active">Active</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="status" id="user_status_inactive"
                                        class="form-check-input coupon_status_inactive" value="inactive">
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
