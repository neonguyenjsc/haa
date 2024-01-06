@extends('index')
@section('content')
    <?php
    if (isset($user) || isset($_GET['user_id'])) {
        $str = '&user_id=' . $user->id;
    } else {
        $str = '';
    }
    ?>
    <div class="page-wrapper">
        <div class="page-content">
            <div class="content mt-4">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <div class="tabbed-card mt-3">
                                <div class="tab-content">
                                    <h4 class="card-title text-uppercase">Thống kê từ ngày <span
                                            class="text-danger">{{$date[0]}}</span> đến
                                        ngày <span class="text-danger">{{$date[1]}}</span>
                                        @if(isset($user))
                                            #username => {{$user->username}}
                                        @endif
                                    </h4>
                                    <select id="change_time" name="selector" onchange="changeTime()">
                                        <option {{($key == 'today') ?'selected' : ''}} value="today">Hôm nay</option>
                                        <option {{($key == 'yesterday') ?'selected' : ''}}  value="yesterday">Hôm qua</option>
                                        <option {{($key == 'this_week') ?'selected' : ''}}  value="this_week">Tuần này</option>
                                        <option {{($key == 'last_week') ?'selected' : ''}}  value="last_week">Tuần trước</option>
                                        <option {{($key == 'this_month') ?'selected' : ''}}  value="this_month">Tháng này</option>
                                        <option {{($key == 'last_month') ?'selected' : ''}}  value="last_month">Tháng Trước</option>
                                    </select>
                                    <div class="tab-pane active" id="profile1">
                                        <div class="p-15 ">
                                            <div class="table-responsive ">
                                                <table id="example " class="table ">
                                                    <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Tên</th>
                                                        <th>Số tiền</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            Tổng tiền tiêu
                                                        </td>
                                                        <td>
                                                            {{number_format($total_out)}}
                                                        </td>
                                                        <td>
                                                            <a target="_blank"
                                                               href="/admin/static/detail?type=total_out&s={{$date[0]}}&e={{$date[1]}}{{$str}}">Chi
                                                                tiết</a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            Tổng tiền nạp
                                                        </td>
                                                        <td>
                                                            {{number_format($total_in)}}
                                                        </td>
                                                        <td>
                                                            <a target="_blank"
                                                               href="/admin/static/log?key=add_coin&s={{$date[0]}}&e={{$date[1]}}{{$str}}">Chi
                                                                tiết</a>
                                                        </td>
                                                    </tr>
                                                    {{--                                                <tr>--}}
                                                    {{--                                                    <td>--}}
                                                    {{--                                                        Tổng Tạo đơn--}}
                                                    {{--                                                    </td>--}}
                                                    {{--                                                    <td>--}}
                                                    {{--                                                        {{number_format($create_order)}}--}}
                                                    {{--                                                    </td>--}}
                                                    {{--                                                    <td>--}}
                                                    {{--                                                        <a target="_blank" href="">Chi tiết</a>--}}
                                                    {{--                                                    </td>--}}
                                                    {{--                                                </tr>--}}
                                                    <tr>
                                                        <td>
                                                            Tổng tiền hoàn
                                                        </td>
                                                        <td>
                                                            {{number_format($create_refund)}}
                                                        </td>
                                                        <td>
                                                            <a target="_blank"
                                                               href="/admin/static/log?key=refund&s={{$date[0]}}&e={{$date[1]}}{{$str}}">Chi
                                                                tiết</a>
                                                        </td>
                                                    </tr>
                                                    {{--                                                <tr>--}}
                                                    {{--                                                    <td>--}}
                                                    {{--                                                        Tổng tiền trừ--}}
                                                    {{--                                                    </td>--}}
                                                    {{--                                                    <td>--}}
                                                    {{--                                                        {{number_format($total_deduction)}}--}}
                                                    {{--                                                    </td>--}}
                                                    {{--                                                    <td>--}}
                                                    {{--                                                        <a target="_blank" href="">Chi tiết</a>--}}
                                                    {{--                                                    </td>--}}
                                                    {{--                                                </tr>--}}
                                                    </tbody>
                                                </table>
                                                {{--                                            @include('All.paginate')--}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            ?>
            <script>
                function changeTime() {
                    let va = $('select[name=selector] option').filter(':selected').val();
                    window.location.href = '/admin/static?key=' + va + '{!! $str !!}';
                }
            </script>
        </div>
    </div>
    <!-- End Content -->
@endsection
