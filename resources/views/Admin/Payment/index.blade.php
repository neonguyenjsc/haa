@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    {{--                    @include('Libs.form_tab_history')--}}
                    <div class="tab-content py-3">
                        <h4>Cài đặt nạp tiền</h4>
                        <div class="tab-pane fade show active">
                            {{--                            @include('Libs.search')--}}
                            <a href="/admin/payment/add"><span class="badge bg-danger">Tạo thông tin nạp tiền</span></a>
                            <div class="table-responsive mt-3">
                                <table id="example" class="table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th class="text-center">Thao tác</th>
                                        <th>Tên ngân hàng</th>
                                        <th>Họ tên</th>
                                        <th>Số tài khoản</th>
                                        <th>Chi nhánh</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(isset($data))
                                        @foreach($data as $i=>$item_data)
                                            <tr>
                                                <td>{{$i+1}}</td>
                                                <td class="text-center">
                                                    <a href="/admin/payment/remove/{{$item_data->id}}"><span
                                                            class="badge bg-warning">Xóa</span></a>
                                                </td>
                                                <td>{{$item_data->name}}</td>
                                                <td>{{$item_data->full_name}}</td>
                                                <td>{{$item_data->stk}}</td>
                                                <td>{{$item_data->branch}}</td>
                                                <td><span
                                                        class="{{$item_data->status_class}}">{{$item_data->status_string}}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                                {{--                                @include('Libs.paginate')--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
