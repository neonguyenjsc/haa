@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/card-config">Lịch sữ nạp thẻ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#"
                               aria-controls="history" aria-selected="false">Cài đặt thẻ</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="order" role="tabpanel" aria-labelledby="home-tab">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="p-15">
                                        <div class="box-header with-border pb-2">
                                            <h4 class="box-title">Cài đặt nạp thẻ<strong></strong>
                                            </h4>
                                        </div>
                                        @include('Libs.success_message')

                                        <form action="/admin/card-config/update-config"
                                              method="POST"
                                              id="order_form">
                                            @foreach($config as $item)
                                                <h5 class="font-weight-bold">Loại thẻ {{$item->name}}:</h5>
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Chiết khấu % ( phí gạch thẻ
                                                        ):</label>
                                                    <input id="object_id" type="number" name="charge[]"
                                                           class="form-control rounded-10"
                                                           value="{{$item->charge}}" required
                                                           placeholder="">
                                                    <input id="object_id" type="hidden" name="id[]"
                                                           class="form-control rounded-10"
                                                           value="{{$item->id}}" required
                                                           placeholder="">
                                                </div>
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Gạch thẻ Thủ công / Auto</label>
                                                    <select name="auto[]" class="form-control">
                                                        <option {{($item->auto==1) ?'selected':''}} value="1">Tự động
                                                            duyệt
                                                        </option>
                                                        <option {{($item->auto== 1) ?'':'selected'}} value="0">Duyệt thủ
                                                            công
                                                        </option>
                                                    </select>
                                                </div>

                                                <hr>
                                            @endforeach
                                            <div class="box-footer">
                                                <button id="btn_buy_order" type="submit"
                                                        class="btn rounded-10 btn-primary font-weight-bold w-100">
                                                    Cập
                                                    nhật
                                                </button>
                                            </div>
                                        </form>


                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-15">
                                        <h5>Tổng tiền gạch tay tháng này</h5>
                                        <div class="table-responsive">
                                            <table id="example" class="table">
                                                <thead>
                                                <tr>
                                                    <th scope="col">username</th>
                                                    <th scope="col">Tổng tiền gạch</th>
                                                    <th scope="col">Tổng tiền trả (20%)</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($month as $i=>$item)
                                                    <tr>
                                                        <td>{{$i}}</td>
                                                        <td>{{number_format($item)}}</td>
                                                        <td>{{number_format($item - ($item*20/100))}}</td>
                                                        {{--                                                    <tr></tr>--}}
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-15">
                                        <h5>Tổng tiền gạch tay tháng trước</h5>
                                        <div class="table-responsive">
                                            <table id="example" class="table">
                                                <thead>
                                                <tr>
                                                    <th scope="col">username</th>
                                                    <th scope="col">Tổng tiền gạch</th>
                                                    <th scope="col">Tổng tiền trả (20%)</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($last_month as $i=>$item)
                                                    <tr>
                                                        <td>{{$i}}</td>
                                                        <td>{{number_format($item)}}</td>
                                                        <td>{{number_format($item - ($item*20/100))}}</td>
                                                        {{--                                                    <tr></tr>--}}
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>

                                <script>
                                </script>
                                {{--                                <div class="col-md-4">--}}
                                {{--                                    <div class="car badge-success mb-4 rounded-10">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex justify-content-between">--}}
                                {{--                                                <div class="d-flex font-19">--}}
                                {{--                                                    <i class="fas fa-wallet mr-2"></i>--}}
                                {{--                                                    <h6 class="mb-0 font-19">Số tiền hiện có</h6>--}}
                                {{--                                                </div>--}}
                                {{--                                                <h6 class="font-19 mb-0 ">{{number_format(Auth::user()->coin ?? 0).' '.getCurrency()}}</h6>--}}
                                {{--                                            </div>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                    <div class="car badge-success mb-4 rounded-10">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <div class="d-flex justify-content-between">--}}
                                {{--                                                <div class="d-flex font-19">--}}
                                {{--                                                    <i class="fas fa-sign-in-alt mr-2"></i>--}}
                                {{--                                                    <h6 class="mb-0 font-19">Số tiền đã nạp</h6>--}}
                                {{--                                                </div>--}}
                                {{--                                                <h6 class="font-19 mb-0 ">{{number_format(Auth::user()->total_recharge ?? 0).' '.getCurrency()}}</h6>--}}
                                {{--                                            </div>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                    <div class="car badge-success rounded-10">--}}
                                {{--                                        <div class="card-body">--}}
                                {{--                                            <h6 class="font-weight-bold">Hướng dẫn nạp tiền: </h6>--}}
                                {{--                                            <h6>Liên hệ admin</h6>--}}

                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
