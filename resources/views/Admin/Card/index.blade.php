@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#order" role="tab"
                               aria-controls="order" aria-selected="true">Lịch sữ nạp thẻ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/card-config/config"
                               aria-controls="history" aria-selected="false">Cài đặt thẻ</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="order" role="tabpanel" aria-labelledby="home-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="p-15">
                                        @include('Libs.success_message')
                                        @include('Libs.search')
                                        <div class="table-responsive">
                                            <table id="example" class="table">
                                                <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Thao tác</th>
                                                    <th scope="col">username</th>
                                                    <th scope="col">Seri</th>
                                                    <th scope="col">Mạng</th>
                                                    <th scope="col">Mệnh giá</th>
                                                    <th scope="col">Hình thức</th>
                                                    <th scope="col">Người duyệt</th>
                                                    <th scope="col">Trạng thái</th>
                                                    <th scope="col">Thực nhận</th>
                                                    <th scope="col">Ghi chú</th>
                                                    <th scope="col">Thời gian</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($data as $i=>$item)
                                                    <tr>
                                                        <th scope="row">{{$i +1}}</th>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button
                                                                    class="btn btn-outline-secondary dropdown-toggle"
                                                                    type="button" data-bs-toggle="dropdown"
                                                                    aria-expanded="false">Thao tác
                                                                </button>
                                                                <ul class="dropdown-menu" style="">
                                                                    @if($item->status == 1)
                                                                        <li>
                                                                            <a href="{{getUri(true)}}/active/{{$item->id}}?status=2"
                                                                               class="dropdown-item"><i
                                                                                    class="simple-icon-cloud-download"></i>Thẻ
                                                                                ok</a>
                                                                        </li>
                                                                        {{--                                                                        <li>--}}
                                                                        {{--                                                                            <a href="{{getUri(true)}}/wrong-amount/{{$item->id}}"--}}
                                                                        {{--                                                                               class="dropdown-item"><i--}}
                                                                        {{--                                                                                    class="simple-icon-printer"></i>Sai--}}
                                                                        {{--                                                                                giá</a>--}}
                                                                        {{--                                                                        </li>--}}
                                                                        <li>
                                                                            <a href="{{getUri(true)}}/active/{{$item->id}}?status=1"
                                                                               class="dropdown-item"><i
                                                                                    class="simple-icon-trash"></i>Thẻ
                                                                                sai</a>
                                                                        </li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        </td>
                                                        <td>{{$item->username}}</td>
                                                        <td>{{$item->serial}}</td>
                                                        <td>{{$item->card}}</td>
                                                        <td>{{number_format($item->amount)}}</td>
                                                        <td>{{($item->type == 'handmade') ? 'Thủ công' : 'Tự động'}}</td>
                                                        <td>{{$item->username_active}}</td>
                                                        <td class="{{$item->status_string['class']}}">{{$item->status_string['text']}}</td>
                                                        <td>{{number_format($item->real_coin)}}</td>
                                                        <td>{{$item->description}}</td>
                                                        <td>{{$item->created_at}}</td>
                                                    </tr>
                                                @endforeach
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
            </div>
        </div>
    </div>
@endsection
