@extends('admin/index')
@section('title', 'CITY')
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>All Cities</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">All Cities</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">

    <div class="container-fluid">

        {{-- $child_category_list --}}
        {{-- $parent_category_list --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-default">Add City</button>
                        <form action="{{url('admin/export-data')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="upload-btn-wrapper">
                                <button class="uploadf">Upload a file</button>
                                <input type="file" name="csv_file" />
                                @if ($errors->has('csv_file'))
                                <div class="form-valid-error">
                                    {{ $errors->first('csv_file') }}
                                </div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-success btn-sm export_btn">Export</button>
                        </form>

                    </div>
                    <div class="card-body">
                        <table id="childcategorylisting" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Country-Name</th>
                                    <th>City-Name</th>
                                    <th>City-Badges</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($citys as $city)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $city->country_name }}</td>
                                    <td>{{ $city->city_name }}</td>
                                    <td><img src="{{ asset('public/admin-assets/img/city') }}/{{ $city->city_badges }}">
                                    </td>
                                    <td>
                                        <a href="" class="btn btn-sm btn-success edit_childcat admin_edit_categories" cat_type="child" city_id="{{ $city->id }}" country_name="{{ $city->country_name }}" city_name="{{ $city->city_name }}" icon_path="{{ asset('public/admin-assets/img/city') }}" city_badges="{{ $city->city_badges }}">Edit</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>S.no</th>
                                    <th>Country-Name</th>
                                    <th>City-Name</th>
                                    <th>City-Badges</th>
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

        <div class="modal fade" id="modal-default">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add City</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="add_new_childcat_form" action="{{ url('admin/insert-city') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="">Country-name</label>
                                    <select name="country_name" id="country_name" class="form-control country_name">
                                        <option value="">Select Country</option>
                                        @foreach ($countrys as $country)
                                        <option value="{{$country->id}}">{{$country->country_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="Inputchildcat_name">City Name</label>
                                    <input type="text" name="city_name" class="form-control city_name" id="" placeholder="Enter Category Name">
                                </div>

                                <div class="form-group">
                                    <label for="Inputchildcat_name">City Badges</label>
                                    <input type="file" name="city_badges" class="form-control city_badges" id="">
                                    <img src="" alt="" class="city_badges mt-1" width="100">
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
    </div>

</section>


@stop