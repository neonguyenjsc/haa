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
                                        <th>Server</th>
                                        <th>Bắt đầu</th>
                                        <th>Số lượng mua</th>
                                        <th>Giá</th>
                                        <th>Tổng tiền</th>
                                        <th>Ngày tạo</th>
                                        <th>Tình trạng</th>
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
                                            <td><a target="_blank" href="{{$item->link}}">{{$item->object_id}}</a></td>
                                            <td>{{$item->server}}</td>
                                            <td>{{$item->start_like}}</td>
                                            <td>{{$item->quantity}}</td>
                                            <td>{{$item->price_per}}</td>
                                            <td>{{$item->prices}}</td>
                                            <td>{{$item->created_at}}</td>
                                            <td>
                                                <span>{{$item->status_string}}</span>
                                            </td>
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
