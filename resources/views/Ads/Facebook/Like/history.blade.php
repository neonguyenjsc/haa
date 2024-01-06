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
                                        <th>Thao tác</th>
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
                                            <td>
                                                @if($item->show_action)
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary dropdown-toggle"
                                                                type="button" data-bs-toggle="dropdown"
                                                                aria-expanded="false">Thao tác
                                                        </button>
                                                        <ul class="dropdown-menu" style="">
                                                            @if($item->allow_remove)
                                                                <li><a class="dropdown-item" style="cursor: pointer"
                                                                       onclick="removeOrder('{{$item->id}}','{{getUriOrder()}}/remove/{{$item->id}}')">Hủy
                                                                        đơn</a>
                                                                </li>

                                                            @endif
                                                            @if($item->allow_warranty)
                                                                <li><a class="dropdown-item" style="cursor: pointer"
                                                                       onclick="warrantyOrder('{{$item->id}}','{{getUriOrder()}}/warranty/{{$item->id}}')">Bảo
                                                                        Hành</a>
                                                                </li>

                                                            @endif

                                                            @if($item->is_check_order)
                                                                <li><a class="dropdown-item" style="cursor: pointer"
                                                                       onclick="checkStatusOrder('{{$item->id}}','{{getUriOrder()}}/check-order/{{$item->id}}')">Kiểm
                                                                        tra số lượng chạy</a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                @endif
                                            </td>
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
