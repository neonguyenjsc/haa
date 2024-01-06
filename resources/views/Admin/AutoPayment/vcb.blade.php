@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    {{--                    @include('Libs.form_tab_history')--}}
                    <div class="tab-content py-3">
                        <div class="tab-pane fade show active">
                            @include('Libs.search')
                            <a href="/admin/auto-payment/vcb/add"><span class="badge bg-danger">Tạo Mã giao dịch</span></a>
                            <div class="table-responsive mt-3">
                                <table class="table align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th class="whitespace-nowrap label-table">#</th>
                                        <th class="whitespace-nowrap label-table">Mã giao dịch</th>
                                        <th class="whitespace-nowrap label-table">Username</th>
                                        <th class="whitespace-nowrap label-table">Coin</th>
                                        <th class="whitespace-nowrap label-table">Trạng thái</th>
                                        <th class="whitespace-nowrap label-table">Thời gian</th>
                                        <th class="whitespace-nowrap label-table">Mô tả</th>
                                        <th class="whitespace-nowrap label-table">Ngày tạo</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($data as $i=>$item)
                                        <tr>
                                            <td>{{$i+1}}</td>
                                            <td>{{$item->trans_id}}</td>
                                            <td>{{$item->username}}</td>
                                            <td>{{number_format($item->coin)}}</td>
                                            <td>
                                                @if($item->status == 1)
                                                    <p class="text-success">{{$item->description}}</p>
                                                @elseif($item->status == 2)
                                                    <p class="text-danger">{{$item->description}}</p>
                                                @else
                                                    <p class="text-primary">{{$item->description}}</p>
                                                @endif
                                            </td>
                                            <td>{{$item->date}}</td>
                                            <td>{{$item->mo_ta}}</td>
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
@endsection
