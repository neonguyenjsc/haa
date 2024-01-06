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
                                        <th>Hết hạn</th>
                                        <th>Server</th>
                                        <th>Số lượng mua</th>
                                        <th>Giá</th>
                                        <th>Tổng tiền</th>
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
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown"
                                                            aria-expanded="false">Thao tác
                                                    </button>
                                                    <ul class="dropdown-menu" style="">
                                                        <li><a class="dropdown-item" style="cursor: pointer"
                                                               onclick="showModalRenew('{{$item->id}}','{{getPricesMin(getInfoUser('level_id'),$item->package_name)}}','{{$item->quantity}}','{{$item->fb_id}}','{{$item->package_name}}','{{$item->server}}','{{$item->total_post}}')">Gia
                                                                Hạn</a>
                                                        </li>
                                                        @if($item->status != 0)
                                                            <li>
                                                                <a class="dropdown-item" style="cursor: pointer"
                                                                   onclick="removeOrderVip('{{$item->id}}','/facebook-vip-clone/remove/{{$item->id}}',5000)">Dừng
                                                                    vip</a>
                                                            </li>
                                                        @endif
                                                    </ul>

                                                </div>
                                            </td>
                                            <td>{{$item->client_username}}</td>
                                            <td><a target="_blank"
                                                   href="https://facebook.com/{{$item->fb_id}}">{{$item->fb_id}}</a>
                                            </td>

                                            <td>{{$item->time_expired}}</td>
                                            <td>{{$item->server}}</td>
                                            <td>{{$item->quantity}}</td>
                                            <td>{{$item->price_per}}</td>
                                            <td>{{$item->prices}}</td>
                                            <td>{{$item->time_exp}}</td>
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
    <div class="modal fade" id="model_renew" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Gia Hạn đơn hàng <span><b id="ma_don_"
                                                                                             style="color: red"></b></span>
                    </h5>
                    {{--                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">--}}
                    {{--                        <span aria-hidden="true">&times;</span>--}}
                    {{--                    </button>--}}
                </div>
                <div class="modal-body">
                    <form action="/facebook-vip-clone/buy" method="post">
                        <div class="form-group">
                            <div>
                                <div>
                                    <label class="bold">Gói <span id="server" style="color: red"></span></label>
                                </div>
                                <label class="bold">Số tháng :</label>
                                <div class="custom-control custom-radio">
                                    <input onclick="checkCoin()" type="radio" id="month1" name="num_day" value="30"
                                           class="custom-control-input">
                                    <label class="custom-control-label" for="month1">1 Tháng</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input onclick="checkCoin()" type="radio" id="month2" name="num_day" value="60"
                                           class="custom-control-input">
                                    <label class="custom-control-label" for="month2">2 Tháng</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input onclick="checkCoin()" type="radio" id="month3" name="num_day" value="90"
                                           class="custom-control-input">
                                    <label class="custom-control-label" for="month3">3 Tháng</label>
                                </div>
                                <input style="display: none" name="id">
                                <input style="display: none" name="price_per">
                                <input style="display: none" name="package_name">
                                <input style="display: none" name="object_id">
                                <input style="display: none" name="slbv" value="5">
                            </div>
                            <div class="form-group">
                                <label class="fw-bold form-label">Id vip</label>
                                <input id="object_id" type="text" disabled
                                       class="form-control rounded-10" required onchange="convertUid()"
                                       placeholder="Nhập ID hoặc Link tùy theo gói">
                            </div>
                            <div class="form-group">
                                <label class="fw-bold form-label">Số lượng</label>
                                <input id="quantity" name="quantity" type="text" value=""
                                       class="form-control rounded-10" required onchange="convertUid()"
                                       placeholder="50,100,150,200,250,300,500,750,1000,1500,2000,3000,5000,75000,100000">
                            </div>
                            <div class="form-group">
                                <label class="fw-bold form-label">Giá tiền</label>
                                <input disabled id="price_per" type="text" value=""
                                       class="form-control rounded-10" required onchange="convertUid()"
                                       placeholder="Nhập ID hoặc Link tùy theo gói">
                            </div>
                            <div class="form-group">
                                <label class="fw-bold form-label">Số lượng bài viết (tùy gói)</label>
                                <input disabled id="total_post" type="text" value=""
                                       class="form-control rounded-10" required onchange="convertUid()"
                                       placeholder="Nhập ID hoặc Link tùy theo gói">
                            </div>
                            <div>
                                <label class="bold">Tổng tiền : <span id="check_out_coin"></span></label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">GIA HẠN</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <script>
        function showModalRenew(id, p, q, object_id, package_name, server, total_post) {
            $('input[name="id"]').val(id);
            $('input[name="price_per"]').val(p);
            $('input[name="quantity"]').val(q);
            $('input[name="package_name"]').val(package_name);
            $('input[name="object_id"]').val(object_id);
            $('input[name="slbv"]').val(total_post);
            $('#price_per').val(p);
            $('#quantity').val(q);
            $('#package_name').val(package_name);
            $('#object_id').val(object_id);
            $('#slbv').val(total_post);
            $('#server').text(server);
            $('#ma_don_').text(id);
            $('#model_renew').modal('toggle');
        }

        function checkCoin() {
            let p = $('input[name="price_per"]').val();
            let q = $('input[name="quantity"]').val();
            let m = getValueRadioByName('num_day');
            console.log(p);
            console.log(q);
            console.log(m);
            let check_out_coin = parseFloat(q) * parseFloat(p) * parseInt(m);
            $('#check_out_coin').text(formatNumber(check_out_coin));
        }
    </script>
@endsection
