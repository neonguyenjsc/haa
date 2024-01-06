@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="row">
                <div class="col-12 col-lg-12 col-xl-6">
                    <a href="/nap-tien" class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <!-- <div class="w_chart easy-dash-chart1" data-percent="60">
                                    <span class="w_percent"></span>
                                </div> -->
                                <div class="ms-3">
                                    <h6 class="mb-0">NẠP TIỀN VÀO TÀI KHOẢN</h6>
                                </div>
                                <div class="ms-auto fs-1 text-facebook">
                                    <i class="fadeIn animated bx bx-chevrons-right"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-lg-12 col-xl-6">
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="card radius-10 bg-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <p class="mb-0 text-dark">Tổng tiền</p>
                                            <h4 class="my-1 text-dark">{{getInfoUser('coin')}}</h4>
                                        </div>
                                        <div class="widgets-icons bg-white text-dark ms-auto">
                                            <i class="bx bxs-wallet"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="card radius-10 bg-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <p class="mb-0 text-dark">Đã nạp tháng này</p>
                                            <h4 class="my-1 text-dark">{{getInfoUser('total_month')}}</h4>
                                        </div>
                                        <div class="text-white ms-auto font-35"><i class="bx bx-dollar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-12 col-xl-6">
                    <div class="card radius-10 w-100">
                        <div class="card-header border-bottom bg-transparent">
                            <div class="d-flex py-2 align-items-center">
                                <div>
                                    <h6 class="mb-0">Thông báo hệ thống</h6>
                                </div>
                                <!-- <div class="font-22 ms-auto"><i class="bx bx-dots-horizontal-rounded"></i></div> -->
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            @foreach($notify as $item)
                                <li class="list-group-item bg-transparent">
                                    <div class="d-flex align-items-center">
                                        <img src="{{getLogo()}}" alt="user avatar"
                                             class="rounded-circle"
                                             width="55" height="55"/>
                                        <div class="ms-3">
                                            <h6 class="mb-0">{{$item->title}}<small
                                                    class="ms-4">{{date('d-m-Y',strtotime($item->created_at))}}</small>
                                            </h6>
                                            <p class="mb-0 small-font">{!! $item->content !!}</p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-lg-12 col-xl-6">
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <a href="https://www.facebook.com/groups/vitaminboyandgirl" class="card radius-10">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="ms-3">
                                            <h6 class="mb-0">Group Facebook</h6>
                                        </div>
                                        <div class="ms-auto fs-1 text-facebook"><i class="bx bxl-facebook"></i></div>
                                    </div>
                                </div>
                            </a>
                            <a href="https://www.facebook.com/Baostar.Community" class="card radius-10">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="ms-3">
                                            <h6 class="mb-0">Group Fanpage</h6>
                                        </div>
                                        <div class="ms-auto fs-1 text-facebook"><i class="bx bxl-facebook"></i></div>
                                    </div>
                                </div>
                            </a>
                            <a href="https://t.me/Home_BaoStar" class="card radius-10">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="ms-3">
                                            <h6 class="mb-0">Group Telegram</h6>
                                        </div>
                                        <div class="ms-auto fs-1 text-facebook">
                                            <img
                                                src="https://maxcdn.icons8.com/Share/icon/win10/Logos/telegram_app1600.png"
                                                width="54"
                                                class="img-fluid"
                                                alt=""/>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <a href="https://zalo.me/0523169999" class="card radius-10">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="ms-3">
                                            <h6 class="mb-0">Group Zalo</h6>
                                        </div>
                                        <div class="ms-auto fs-1 text-facebook">
                                            <img src="..//assets/images/custom/icon-zalo.png" width="54"
                                                 class="img-fluid"
                                                 alt=""/>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <a href="/rating" class="card radius-10">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="ms-3">
                                            <h6 class="mb-0">Đánh giá chất lượng</h6>
                                        </div>
                                        <div class="ms-auto fs-1 text-facebook">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/99/Star_icon_stylized.svg/512px-Star_icon_stylized.svg.png" width="54"
                                                 class="img-fluid"
                                                 alt=""/>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 col-lg-6">

                            <div class="card radius-10 w-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h5 class="mb-1">Sản phầm</h5>
                                        </div>
                                    </div>
                                </div>
                                <div style="height: 100% !important;" class="product-list p-3 mb-3">
                                    <a href="/api-tich-hop"
                                       class="row border mx-0 mb-3 py-2 radius-10 cursor-pointer">
                                        <div class="d-flex align-items-center">
                                            <div class="ms-2 py-3">
                                                <h6 class="mb-1">Tài liệu API</h6>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="https://docs.google.com/spreadsheets/d/1QHmUTaZHn4OI0Fm1ReTocuxrdV02vUF3TZ8aSBAxILk/edit?usp=sharing"
                                       class="row border mx-0 mb-3 py-2 radius-10 cursor-pointer">
                                        <div class="d-flex align-items-center">
                                            <div class="ms-2 py-3">
                                                <h6 class="mb-1">Bảng giá dịch vụ</h6>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="https://docs.google.com/spreadsheets/d/1GPX_392hwmVEwsRdsN2-C751BkzF6uZ3U3_T9t8-GOg/edit?usp=sharing"
                                       class="row border mx-0 mb-3 py-2 radius-10 cursor-pointer">
                                        <div class="d-flex align-items-center">
                                            <div class="ms-2 py-3">
                                                <h6 class="mb-1">Mua Page</h6>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="https://www.youtube.com/watch?v=bPzNNfBBCn0&t=84s&ab_channel=B%C3%A1oStar"
                                       class="row border mx-0 mb-3 py-2 radius-10 cursor-pointer">
                                        <div class="d-flex align-items-center">
                                            <div class="ms-2 py-3">
                                                <h6 class="mb-1">Học thuật Facebook miễn phí</h6>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!--End Row-->
        </div>
    </div>
    @include('Layout.modal',['item'=>$item])
    <script>
        $(document).ready(function () {
            $('#modal_notify_home').modal('toggle');
        });
    </script>
@endsection
