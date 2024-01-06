@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    @include('Libs.form_tab_history')
                    <div class="tab-content py-3">
                        <h4>{{$menu->name}}</h4>
                        <div class="tab-pane fade show active">
                            @include('Libs.search')
                            <div class="table-responsive mt-3">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Thời gian tạo</th>
                                        <th>Ghi chú</th>
                                        <th>Username</th>
                                        <th>ip</th>
                                        <th>port</th>
                                        <th>username</th>
                                        <th>password</th>
                                        <th>Gói</th>
                                        <th>Hạn</th>
                                        <th>Tổng tiền</th>
                                        <th>Số ngày</th>
                                        <th>Ngày tạo</th>
                                        {{--                                            <th class="text-center">Thao tác</th>--}}
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(isset($data))
                                        @foreach($data as $i=>$item)
                                            <tr>
                                                <td>{{$i+1}}</td>
                                                <td>{{{$item->created_at ?? ''}}}</td>
                                                <td>{{{$item->notes}}}</td>
                                                <td>{{{$item->client_username ?? $item->username}}}</td>
                                                <td>{{$item->ip}}</td>
                                                <td>{{$item->port}}</td>
                                                <td>{{$item->proxy_username}}</td>
                                                <td>{{$item->proxy_password}}</td>
                                                <td>{{$item->server}}</td>
                                                <td>{{date('d-m-Y H:i:s',$item->time_end)}}</td>
                                                <td>{{number_format($item->prices)}}</td>
                                                <td>{{number_format($item->quantity)}}</td>
                                                <td>
                                                    {{$item->created_at}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
