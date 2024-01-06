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
                                        <th>Username</th>
                                        <th>Object Id</th>
                                        <th>Số lượng</th>
                                        <th>Trạng thái</th>
                                        <th>Tổng tiền</th>
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
                                                <td>{{{$item->client_username ?? $item->username}}}</td>
                                                <td>
                                                    <a href="{{$item->link}}"
                                                       target="_blank">{{$item->object_id}}</a>
                                                </td>
                                                <td>
                                                    {{number_format($item->quantity)}}
                                                </td>
                                                <td class="{{$item->status_class}}">
                                                    {{$item->status_string}}
                                                </td>
                                                <td>
                                                    <h6 class="mb-0 font-weight-bold">{{number_format($item->prices)}}
                                                    </h6>
                                                </td>
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
