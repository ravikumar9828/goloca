@extends('admin/index')
@section('title', 'COUNTRY')
@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Add Country</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">All Country</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            {{-- $category_list --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <button type="button" class="btn btn-success float-right" data-toggle="modal"
                                data-target="#modal-default">Add Country</button>
                        </div>
                        <div class="card-body">
                            <table id="parentcategorylisting" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>S.no</th>
                                        <th>Country-name</th>
                                        <th>Country-Badges</th>
                                        {{-- <th>status</th> --}}
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($countries as $category)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $category->country_name }}</td>
                                            <td> <img src="{{ asset('public/admin-assets/img/country/country_badges') }}/{{ $category->badges }}"> </td>
                                            <td>
                                                <a href="" class="edit_parentcat admin_edit_categories btn btn-success btn-sm"
                                                    cat_type="parent" id="{{ $category->id }}"
                                                    country_name="{{ $category->country_name }}"
                                                    badges_path="{{ asset('public/admin-assets/img/country/country_badges') }}" badges_icon="{{ $category->badges }}">Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>S.no</th>
                                        <th>Country-name</th>
                                        <th>Country-Badges</th>
                                        {{-- <th>status</th> --}}
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
                            <h4 class="modal-title">Add Country</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="add_new_parentcat_form" action="{{ url('admin/insert-countries') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">

                                    <div class="form-group">
                                        <label for="">Country Name</label>
                                        <input type="text" name="country_name" class="form-control country_name"
                                            placeholder="Enter country Name">
                                    </div>

                                    <div class="form-group">
                                        <label for="">Country Basges</label>
                                        <input type="file" name="badges" class="form-control badges" value="">
                                        <img src="" alt="" class="badges mt-2" width="100">
                                    </div>

                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary button">Submit</button>
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
@endsection
