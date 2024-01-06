@extends('index')
@section('content')
    <?php
    function getCoin($old_coin, $new_coin)
    {
        if ($new_coin >= $old_coin) {
            $coin = $new_coin - $old_coin;
            return "<span class='badge badge-info'>" . number_format($old_coin) . "</span>+<span class='badge badge-warning'>" . number_format($coin) . "</span>=<span class='badge badge-success'>" . number_format($new_coin) . "</span>";
        } else {
            $coin = $new_coin - $old_coin;
            return "<span class='badge badge-info'>" . number_format($old_coin) . "</span>-<span class='badge badge-warning'>" . number_format($coin) . "</span>=<span class='badge badge-primary'>" . number_format(abs($new_coin)) . "</span>";
        }
    }
    ?>
    <div class="page-wrapper">
        <div class="page-content">
            <h6 class="mb-0 text-uppercase">Lịch sử
            </h6>
            <hr/>
            <div class="card">
                <div class="card-body">
                    @include('Libs.search_log')
                    <div class="table-responsive mt-3">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>username</th>
                                <th>Mã đơn</th>
                                <th>username CTV</th>
                                <th>Hành động</th>
                                <th>Đối tượng</th>
                                <th>Tiền</th>
                                <th>Thời gian tạo</th>
                                <th>Note</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data as $i=>$item)
                                <tr class="intro-x">
                                    <td>
                                        {{$i+1}}
                                    </td>
                                    @if(isAdmin())
                                        <td>{{$item->username}}</td>
                                    @endif
                                    <td>{{$item->orders_id}}</td>
                                    <td>{{$item->client_username}}</td>
                                    <td>{{$item->type_str ?? 'Nạp tiền'}}</td>
                                    <td>{{$item->object_id}}</td>
                                    @if($item->action_coin =='in')
                                        <td class="text-center whitespace-nowrap">
                                            <p>
                                                                        <span
                                                                            class="text-primary">{{number_format($item->old_coin)}} </span>
                                                +
                                                <span
                                                    class="text-danger">{{number_format($item->coin)}}</span>
                                                =
                                                <span
                                                    class="text-success">{{number_format($item->new_coin)}}</span>
                                            </p>
                                        </td>
                                    @else
                                        <td class="text-center whitespace-nowrap">
                                            <p>
                                                                        <span
                                                                            class="text-primary">{{number_format($item->old_coin)}} </span>
                                                -
                                                <span
                                                    class="text-danger">{{number_format($item->coin)}}</span>
                                                =
                                                <span
                                                    class="text-success">{{number_format($item->new_coin)}}</span>
                                            </p>
                                        </td>
                                    @endif
                                    <td>{{$item->created_at}}</td>
                                    <td>{{$item->description}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
{{--                        @include('Libs.paginate')--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
