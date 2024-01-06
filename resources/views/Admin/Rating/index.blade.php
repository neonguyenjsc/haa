@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    {{--                    @include('Libs.form_tab_history')--}}
                    <div class="tab-content py-3">
                        <h4>{{$menu->name}}</h4>
                        <div class="tab-pane fade show active">
                            @include('Libs.search')
                            <div class="table-responsive mt-3">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>username</th>
                                        <th>0 Sao</th>
                                        <th>1 Sao</th>
                                        <th>2 Sao</th>
                                        <th>3 Sao</th>
                                        <th>4 Sao</th>
                                        <th>5 Sao</th>
                                        <th>Trung bình sao</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($account as $i=>$item_c)
                                        <tr>
                                            <td>{{$item_c->username}}</td>
                                            <td>{{count($item_c->rating_0)}}</td>
                                            <td>{{count($item_c->rating_1)}}</td>
                                            <td>{{count($item_c->rating_2)}}</td>
                                            <td>{{count($item_c->rating_3)}}</td>
                                            <td>{{count($item_c->rating_4)}}</td>
                                            <td>{{count($item_c->rating_5)}}</td>
                                            <td>{{$item_c->average}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                {{--                                @include('Libs.paginate')--}}
                            </div>
                            <br>
                            <h3>Danh sách các đánh giá</h3>
                            <div class="table-responsive mt-3">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>username đánh giá</th>
                                        <th>Số sao</th>
                                        <th>Nhân viên</th>
                                        <th>Nội dung</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($data as $i=>$item_c)
                                        <tr>
                                            <td>{{$item_c->username}}</td>
                                            <td>{{$item_c->start}}</td>
                                            <td>{{$item_c->username_admin}}</td>
                                            <td>{{$item_c->content}}</td>
                                        </tr>
                                    @endforeach
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
