@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    {{--                    @include('Libs.form_tab_history')--}}
                    <div class="tab-content py-3">
                        <h4>{{$menu->name}}</h4>s
                        <div class="tab-pane fade show active">
                            {{--                            @include('Libs.search')--}}
                            <div class="table-responsive mt-3">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Xem</th>
                                        <th>Tên</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><a href="/admin/auto-payment/vcb">Xem</a></td>
                                        <td>Quản lý nạp tiền Vietcombank</td>
                                    </tr>
                                    <tr>
                                        <td><a href="/admin/auto-payment/momo">Xem</a></td>
                                        <td>Quản lý nạp tiền momo</td>
                                    </tr>
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
