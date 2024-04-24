@extends ('admin/index')

@section('styles')
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
@endsection

@section('content')
    <!--modal body-->
    <div class="jumbotron"style="width:1200px">
        <h1></span>Gallery Image</h1>
    </div>

    <div>

        <ul class="list-inline">
            @foreach ($gallerys as $keys => $gallery)
                <li><a href="#myGallery"data-slide-to="{{ $keys }}"><img class="img-thumbnail"
                src="{{ asset('public/admin-assets/img/gallery/' . $gallery->images) }}" data-toggle="modal"
                data-target="#myModal" style="height:200px;width:250px; margin:5px 0 5px 0;"></a></li>
            @endforeach

        </ul>


        <!--modal body-->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">

                        <button type="button" class="close" data-dismiss="modal" title="Close">
                            <span class="glyphicon glyphicon-remove"></span></button>
                    </div>
                    <div class="modal-body">

                        <!-- carousel body-->
                        <div id="myGallery" class="carousel slide" data-interval="false">
                            <div class="carousel-inner">

                                @foreach ($gallerys as $key => $gallery)
                                    @if ($key == 0)
                                        <div class="item active">
                                            <img src="{{ asset('public/admin-assets/img/gallery/' . $gallery->images) }}"
                                                alt="item1" style="height:400px;width:600px;">
                                            <div class="carousel-caption">
                                            </div>
                                        </div>
                                    @else
                                        <div class="item">
                                            <img src="{{ asset('public/admin-assets/img/gallery/' . $gallery->images) }}"
                                                alt="item1" style="height:400px;width:600px;">
                                            <div class="carousel-caption">
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                <!-- Previous and Next buttons-->
                                <a class="left carousel-control" href="#myGallery" role="button" data-slide="prev">
                                    <span class="glyphicon glyphicon-chevron-left"></span></a>
                                <a class="right carousel-control" href="#myGallery" role="button" data-slide="next">
                                    <span class="glyphicon glyphicon-chevron-right"></span></a>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <div class="pull-left">

                            </div>
                            <button class="btn-sm close" type="button" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>




        </div>
    @endsection

    @section('scripts')
        <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    @endsection
