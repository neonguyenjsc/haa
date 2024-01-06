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
                                        <th>Mã đơn</th>
                                        @if(isAdmin())
                                            <th>username</th>
                                        @endif
                                        <th>username Đại lý</th>
                                        <th>Object id</th>

                                        <th>Hết hạn</th>
                                        <th>Server</th>
                                        <th>Số lượng</th>
                                        <th>Số ngày thuê</th>
                                        <th>Giá</th>
                                        <th>Tổng tiền</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($data as $i=>$item)
                                        <tr>
                                            <td>{{$item->id}}</td>
                                            @if(isAdmin())
                                                <td>{{$item->username}}</td>
                                            @endif
                                            <td>{{$item->client_username}}</td>
                                            <td><a target="_blank"
                                                   href="https://facebook.com/{{$item->fb_id}}">{{$item->fb_id}}</a>
                                            </td>

                                            <td>{{$item->time_expired}}</td>
                                            <td>{{$item->server}}</td>
                                            <td>{{$item->quantity}}</td>
                                            <td>{{$item->days}}</td>
                                            <td>{{$item->price_per}}</td>
                                            <td>{{$item->prices}}</td>
                                            <td>{{$item->notes}}</td>
                                        </tr>
                                    @endforeach
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
