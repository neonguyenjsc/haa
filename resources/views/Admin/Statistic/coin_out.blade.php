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
                                        <h4 class="card-title text-uppercase">Thống kê <SPAN CLASS="text-danger">TRỪ TIỀN</SPAN>
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
                                                            <th>Số tiền</th>
                                                            <th>Key</th>
                                                            <th>Thao tác</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($data as $i=>$item)
                                                            <tr>
                                                                <td>{{$item['name'] ?? ''}}</td>
                                                                <td>{{number_format($item['coin'])}}</td>
                                                                <td>{{$item['key']}}</td>
                                                                @if($item['key'] == 'buy')
                                                                    <td><a target="_blank"
                                                                           href="/admin/static/detail-create-jobs?s={{$date['s']}}&e={{$date['e']}}{{$str}}">Chi
                                                                            tiết</a></td>
                                                                @else
                                                                    <td><a target="_blank"
                                                                           href="/admin/static/log?key={{$item['key']}}&s={{$date['s']}}&e={{$date['e']}}{{$str}}">Chi
                                                                            tiết</a></td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
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
            <!-- End Content -->
        </div>
    </div>

@endsection
