<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GOLOCA Dashboard</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('public/admin-assets/img/Goloca_Icon.png') }}">
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    {{-- <link rel="stylesheet" href="{{ asset('public/admin-assets/plugins/fontawesome-free/css/all.min.css') }}"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <!-- DataTables -->
    <link rel="stylesheet"
        href="{{ asset('public/admin-assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <!--   <link rel="stylesheet" href="{{ asset('public/admin-assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/admin-assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> -->

    <!-- overlayScrollbars -->
    <link rel="stylesheet"
        href="{{ asset('public/admin-assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('public/admin-assets/dist/css/adminlte.min.css') }}">
    <!-- TOASTR -->
    <link rel="stylesheet" href="{{ asset('public/admin-assets/plugins/toastr/toastr.min.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('public/admin-assets/plugins/summernote/summernote-bs4.min.css') }}">

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"
        integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

    <!-----Custom Style Sheet------->
    <link rel="stylesheet" href="{{ asset('public/admin-assets/assets/css/style.css') }}">



    <!-- jQuery -->
    <script src="{{ asset('public/admin-assets/plugins/jquery/jquery.min.js') }}"></script>
    @yield ("styles")
    <style>
        
.user-panel img {
  height: 3.1rem !important;
  width: 3.1rem !important;
}
    </style>

</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__wobble" src="{{ asset('public/admin-assets/dist/img/AdminLTELogo.png') }}"
                alt="AdminLTELogo" height="60" width="60">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>

            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->

                <!----Legacy User menu---->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        @if (Auth::guard('web')->check() == true)
                            <img src="{{ asset('public/admin-assets/assets/img/profile_img/admin/') }}/{{ auth()->user()->pro_img }}"
                                class="user-image img-circle elevation-2" alt="User Image">
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header bg-primary">
                            <img src="{{ asset('public/admin-assets/assets/img/profile_img/admin/') }}/{{ auth()->user()->pro_img }}"
                                class="img-circle elevation-2" alt="User Image">

                            <p> {{ auth()->user()->name }} </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="{{ route('admin.getinfo') }}" class="btn btn-default btn-flat">Profile</a>
                            <a href="{{ route('admin.logout') }}" class="btn btn-default btn-flat float-right">Sign
                                out</a>
                        </li>
                    </ul>
                </li>


            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">


            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="{{ asset('public/admin-assets/assets/img/profile_img/admin/') }}/{{ auth()->user()->pro_img }}"
                            class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ auth()->user()->name }}</a>
                    </div>
                </div>

                @if (Session::has('flash-success'))
                    <p class="admin-toastr" onclick="toastr_success('{{ session('flash-success') }}')"></p>
                @endif
                @if (Session::has('flash-error'))
                    <p class="admin-toastr" onclick="toastr_danger('{{ session('flash-error') }}')"></p>
                @endif


                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class
                 with font-awesome or any other icon font library -->


                        <li class="nav-item ">
                            <a href="{{ route('admin.dashboard') }}"
                                class="nav-link {{ Route::currentRouteName() == 'admin.dashboard' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        @if (Auth::guard('web')->check() == true)
                            <!----All-Users----->
                            <li class="nav-item">
                                <a href="{{ route('all_user') }}"
                                    class="nav-link {{ Route::currentRouteName() == 'all_user' ? 'active' : '' }}">
                                    <i class="nav-icon fa fa-users"></i>
                                    <p>Users</p>
                                </a>
                            </li>
                        @endif

                        <li class="nav-item ">
                            <a href="{{ route('couponCode') }}"
                                class="nav-link {{ Route::currentRouteName() == 'couponCode' ? 'active' : '' }}">
                                <i class="fa-solid fa-ticket"></i>
                                <p>Coupon Code</p>
                            </a>
                        </li>

                        <li class="nav-item ">
                            <a href="{{ route('travelJourney') }}"
                                class="nav-link {{ Route::currentRouteName() == 'travelJourney' ? 'active' : '' }}">
                                <i class="fa-solid fa-location-pin"></i>
                                <p>Check-In</p>
                            </a>
                        </li>

                        <li class="nav-item ">
                            <a href="{{ route('userFeeds') }}"
                                class="nav-link {{ Route::currentRouteName() == 'userFeeds' ? 'active' : '' }}">
                                <i class="fa-solid fa-icons"></i>
                                <p>Users Feeds</p>
                            </a>
                        </li>

                        <!----All Categories----->
                        <li
                            class="nav-item {{ Route::currentRouteName() == 'showCountry' || Route::currentRouteName() == 'showCitys' ? 'menu-open' : '' }}">
                            <a href=""
                                class="nav-link {{ Route::currentRouteName() == 'showCountry' || Route::currentRouteName() == 'showCitys' ? 'active' : '' }}">
                                <i class="fa-solid fa-list"></i>
                                <p>Categories
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('showCountry') }}"
                                        class="nav-link {{ Route::currentRouteName() == 'showCountry' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Country</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('showCitys') }}"
                                        class="nav-link {{ Route::currentRouteName() == 'showCitys' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>City</p>
                                    </a>
                                </li>
                            </ul>

                        </li>

                        <li class="nav-item ">
                            <a href="{{ route('allpageslist') }}"
                                class="nav-link {{ Route::currentRouteName() == 'allpageslist' ? 'active' : '' }}">
                                <i class="fa-solid fa-file"></i>
                                <p>Pages</p>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a href="{{ route('get_goloca_prize_list') }}"
                                class="nav-link {{ Route::currentRouteName() == 'get_goloca_prize_list' ? 'active' : '' }}">
                                <i class="fa-solid fa-ticket"></i>
                                <p>goloca prize</p>
                            </a>
                        </li>

                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield ("content")
        </div>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <!-- <footer class="main-footer">
      <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
      All rights reserved.
      <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.2.0-rc
      </div>
    </footer> -->
    </div>
    <!-- ./wrapper -->

    <script type="text/javascript">
        var base_url = "<?php echo url('') . '/'; ?>"
        var csrf_token = "{{ csrf_token() }}"
    </script>

    <!-- REQUIRED SCRIPTS -->

    <!-- Bootstrap -->
    <script src="{{ asset('public/admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>


    <!-- overlayScrollbars -->
    <script src="{{ asset('public/admin-assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!--public AdminLTE App -->
    <script src="{{ asset('public/admin-assets/dist/js/adminlte.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('public/admin-assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- jquery-validation -->
    <script src="{{ asset('public/admin-assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>

    <!-- PAGE PLUGINS -->
    <!-- jQuery Mapael -->
    <!-- <script src="{{ asset('public/admin-assets/plugins/jquery-mousewheel/jquery.mousewheel.js') }}"></script>
<script src="{{ asset('public/admin-assets/plugins/raphael/raphael.min.js') }}"></script>
<script src="{{ asset('public/admin-assets/plugins/jquery-mapael/jquery.mapael.min.js') }}"></script>
<script src="{{ asset('public/admin-assets/plugins/jquery-mapael/maps/usa_states.min.js') }}"></script> -->
    <!-- ChartJS -->
    <script src="{{ asset('public/admin-assets/plugins/chart.js/Chart.min.js') }}"></script>
    <!-- toastr -->
    <script src="{{ asset('public/admin-assets/plugins/toastr/toastr.min.js') }}"></script>
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('public/admin-assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('public/admin-assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('public/admin-assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}">
    </script>
    <script src="{{ asset('public/admin-assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}">
    </script>
    <script src="{{ asset('public/admin-assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('public/admin-assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('public/admin-assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('public/admin-assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('public/admin-assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('public/admin-assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('public/admin-assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('public/admin-assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('public/admin-assets/assets/js/adminjs.js') }}"></script>

    <!-- SweetAlert2 -->
    <!-- <script src="{{ asset('public/admin-assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    @yield ("scripts")




</body>

</html>
