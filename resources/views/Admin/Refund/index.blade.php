@extends('index')
@section('content')
    <?php
    function checkActiveStatusRefund($status)
    {
        $status_ = $_GET['status'] ?? 0;
        if ($status == $status_) {
            return 'active';
        }
        return '';
    }
    ?>
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    {{--                    @include('Libs.form_tab_history')--}}
                    <div class="tab-content py-3">
                        <h4>{{$menu->name}}</h4>
                        <div class="tab-pane fade show active">
                            @include('Libs.search')
                            <hr>
                            <ul class="nav nav-pills navtab-bg nav-justified">
                                {{--                                <li class="nav-item">--}}
                                {{--                                    <a href="{{getUri(true)}}?status=0" aria-expanded="false"--}}
                                {{--                                       class="nav-link {{checkActiveStatusRefund(0)}}">--}}
                                {{--                                        Chưa hoàn--}}
                                {{--                                    </a>--}}
                                {{--                                </li>--}}
                                <li class="nav-item">
                                    <a href="{{getUri(true)}}?status=-3" aria-expanded="false"
                                       class="nav-link {{checkActiveStatusRefund(-3)}}">
                                        Tạo đơn lỗi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{getUri(true)}}?status=0" aria-expanded="false"
                                       class="nav-link {{checkActiveStatusRefund(0)}}">
                                        Chưa hoàn Auto
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{getUri(true)}}?status=1" aria-expanded="true"
                                       class="nav-link {{checkActiveStatusRefund(1)}}">
                                        Đã hoàn
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{getUri(true)}}?status=-1" aria-expanded="true"
                                       class="nav-link {{checkActiveStatusRefund(-1)}}">
                                        Chờ đếm Auto
                                    </a>
                                </li>
                            </ul>
                            <hr>
                            <div class="table-responsive mt-3">
                                <table id="example " class="table ">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Hành động
                                            <input type="checkbox" onclick="checkAll()"
                                                   name="check_all">
                                        </th>
                                        <th>Trạng thái

                                        </th>
                                        <th>uid</th>
                                        <th>Số lượng mua</th>
                                        <th>giá tiền</th>
                                        <th>Chưa chạy</th>
                                        <th>Tổng hoàn</th>
                                        <th>Nội dung</th>
                                        <th>username</th>
                                        <th>username con</th>
                                        <th>username cháu</th>
                                        <th>Mã đơn</th>
                                        <th>Server</th>
                                        <th>Nguồn</th>
                                        <th>Ngày tạo</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <form action="/admin/refund/multi-refund" method="post" id="1">
                                        @foreach($data as $i=>$item)
                                            <tr class="{{$item->action_type ? 'text-success' : 'text-danger'}}">
                                                <td>{{$item->id}}</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary dropdown-toggle"
                                                                type="button" data-bs-toggle="dropdown"
                                                                aria-expanded="false">Thao tác
                                                        </button>
                                                        <ul class="dropdown-menu" style="">
                                                            <li>
                                                                <a class="dropdown-item"
                                                                   href="{{getUri(true)}}/refund-item/{{$item->id}}"><span
                                                                        class="">Hoàn tiền</span></a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item"
                                                                   href="{{getUri(true)}}/delete/{{$item->id}}"><span
                                                                        class="">Xóa</span></a>

                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item"
                                                                   href="/lich-su?key={{$item->object_id}}&limit=500"
                                                                   target="_blank"><span class="">Check Log</span></a>

                                                            </li>

                                                        </ul>
                                                    </div>
                                                    <a>
                                                        <input type="checkbox" name="id[]"
                                                               value="{{$item->id}}">
                                                    </a>
                                                    {{--                                                    <a>--}}
                                                    {{--                                                        <input type="checkbox" name="id[]"--}}
                                                    {{--                                                               value="{{$item->id}}">--}}
                                                    {{--                                                    </a><br>--}}

                                                    {{--                                                    <a href="{{getUri(true)}}/edit/{{$item->id}}"><span--}}
                                                    {{--                                                            class="badge badge-info">Cập nhật</span></a>--}}

                                                </td>
                                                <td><span
                                                        class="badge badge-{{($item->status == 1) ? 'success' : 'warning'}}">{{($item->status == 1) ? 'Đã duyệt' : 'chưa duyệt'}}</span>
                                                </td>
                                                <td>{{$item->object_id}}</td>
                                                <td>{{$item->quantity_buy}}</td>
                                                <td>{{$item->price_per}}</td>
                                                <td>{{$item->quantity}}</td>
                                                <td>{{$item->coin}}</td>
                                                <td>{!! $item->description !!}</td>
                                                <td>{{$item->username}}</td>
                                                <td>{{$item->client_username}}</td>
                                                <td>{{$item->username_agency_lv2}}</td>
                                                <td>{{$item->orders_id}}</td>
                                                <td>{{$item->server}}</td>
                                                <td>
                                                    @if($item->buy_error == 1)
                                                        <span class="text-danger">Lỗi tạo đơn</span>
                                                    @else
                                                        <span class="text-warning">Hủy đơn</span>
                                                    @endif
                                                </td>
                                                <td>{{$item->created_at}}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td></td>
                                            <td>
                                                <button type="submit">Hoàn</button>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </form>
                                    </tbody>
                                </table>
                                @include('Libs.paginate')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function checkAll() {
            $('input:checkbox').each(function () {
                $(this).prop('checked', true);
            });
        }
    </script>
@endsection
