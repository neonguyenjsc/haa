@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content py-3">
                        <h4>{{$menu->name}}</h4>
                        <div class="tab-pane fade show active">
                            <h6><a href="/admin/khach-hang"><span
                                        class="badge bg-warning">Quay lại</span></a></h6>
                            <div class="box-header with-border pb-2">
                                <h4 class="box-title"><strong>Cộng tiền username #<span
                                            style="color: red">{{$user->username}}</span></strong></h4>
                            </div>
                            @include('Libs.success_message')
                            <form action="/admin/khach-hang/add-coin" method="POST"
                                  id="order_form">
                                <div class="box-body">
                                    <input name="id" value="{{$user->id}}" type="hidden"
                                           class="col-sm-3 font-bold d-none">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Nhập số tiền <span
                                                id="coin_txt" style="color: red;"></span></label>
                                        <input name="coin" id="coin" type="number"
                                               onchange="convertTxt()"
                                               class="form-control rounded-10" value="0"
                                               required placeholder="Nhập Id">
                                        <p>Số tiền hiện tại của user ( {{number_format($user->coin)}} )</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tâm thư gửi user</label>
                                        <input name="notes"  type="text"
                                               class="form-control rounded-10" value="admin đã + tiền cho bạn"
                                               required placeholder="notes">
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button id="btn_buy_order"
                                            class="btn rounded-10 btn-primary font-weight-bold w-100">
                                        Cộng tiền
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function convertTxt() {
            $('#coin_txt').text(formatNumber(parseInt($('#coin').val())));
        }
    </script>
@endsection
