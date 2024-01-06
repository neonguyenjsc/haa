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
            <div class="content-page">
                <!-- Start Content -->
                <div class="content mt-4">
                    <div class="container-fluid">
                        <div class="card">
                            <div class="card-body">
                                <div class="tabbed-card mt-3">
                                    <div class="tab-content">
                                        <h4 class="card-title text-uppercase">Thống kê <SPAN CLASS="text-danger">Tạo jobs</SPAN>
                                            từ ngày <span
                                                class="text-danger">{{$date['s']}}</span> đến
                                            ngày <span class="text-danger">{{$date['e']}}</span></h4>
                                        <div class="tab-pane active" id="profile1">
                                            <div class="p-15 ">
                                                <div class="table-responsive ">
                                                    <table id="example " class="table ">
                                                        <thead>
                                                        <tr>
                                                            <th>Tên</th>
                                                            <th>Tổng số đơn</th>
                                                            <th>Số tiền</th>
                                                            <th>Key</th>
                                                            <th>logs</th>
                                                            <th>đơn</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        $coin = 0;
                                                        ?>
                                                        @foreach($data as $i=>$item)
                                                            <?php
                                                            $coin += $item['coin'];
                                                            ?>
                                                            <tr>
                                                                <td>{{$item['prices']->name ?? ''}}</td>
                                                                <td>{{number_format($item['total'])}}</td>
                                                                <td>{{number_format($item['coin'])}}</td>
                                                                <td>{{$item['key']}}</td>
                                                                <td><a target="_blank"
                                                                       href="/admin/static/log?package_name={{$item['key']}}&s={{$date['s']}}&e={{$date['e']}}{{$str}}">Chi
                                                                        tiết Logs</a></td>
                                                                <td><a target="_blank"
                                                                       href="{{$item['menu']->path}}/nhat-ky?package_name={{$item['key']}}&s={{$date['s']}}&e={{$date['e']}}{{$str}}">Chi
                                                                        tiết Đơn</a></td>
                                                            </tr>
                                                        @endforeach
                                                        <tr>
                                                            <td>Tổng tiền</td>
                                                            <td></td>
                                                            <td>{{number_format($coin)}}</td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                    {{--                                            <h1></h1>--}}
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
            <!-- End Content -->
        </div>
    </div>

@endsection
