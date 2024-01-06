@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    {{--                    @include('Libs.form_tab_history')--}}
                    <div class="tab-content py-3">
                        <div class="tab-pane fade show active">
                            {{--                            @include('Libs.search')--}}
                            <a href="/admin/notify/add"><span class="badge bg-danger">Tạo thông báo</span></a>
                            <div class="table-responsive mt-3">
                                <table class="table align-middle mb-0">

                                    <thead>
                                    <tr>
                                        <th class="whitespace-nowrap label-table">#</th>
                                        <th class="whitespace-nowrap label-table">Thao tác</th>
                                        <th class="whitespace-nowrap label-table">Tiêu đề</th>
                                        <th class="whitespace-nowrap label-table">Nội dung</th>
                                        <th class="whitespace-nowrap label-table">Ngày tạo</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($data as $i=>$item)
                                        <tr>
                                            <td>{{$i+1}}</td>
                                            <td><a href="/admin/notify/delete/{{$item->id}}">Xóa</a></td>
                                            <td>{{$item->title}}</td>
                                            <td>{!! $item->content !!}</td>
                                            <td>{{$item->created_at}}</td>
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

