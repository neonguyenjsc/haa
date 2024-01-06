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
                            {{--                            @include('Libs.search')--}}
                            <div class="table-responsive mt-3">
                                <table class="table table-report">
                                    <thead>
                                    <tr>
                                        <th class="whitespace-nowrap label-table">#</th>
                                        <th class="whitespace-nowrap label-table">Thao tác</th>
                                        <th class="whitespace-nowrap label-table">Tên menu</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($data as $i=>$item)
                                        <tr class="intro-x">
                                            <td>{{$i+1}}</td>
                                            <td>
                                                <a href="/admin/package/menu/{{$item->id}}"><span
                                                        class="badge bg-primary">Xem</span></a>

                                            </td>
                                            <td>{{$item->name}}</td>
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
