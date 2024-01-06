@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content py-3">
                        <h4>Thêm mã giao dịch momo</h4>
                        <div class="tab-pane fade show active">
                            <div class="box-header with-border pb-2">
                                @include('Libs.success_message')
                            </div>
                            <h6><a href="/admin/auto-payment/momo"><span
                                        class="badge bg-warning">Quay lại</span></a></h6>
                            <form action="/admin/auto-payment/momo/add" method="POST" id="order_form">
                                <div class="box-body">
                                    <input type="hidden" name="id" value="1">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Mã giao dịch <span class="text-danger">Lưu ý mã giao dịch phải giống ví dụ</span></label>
                                        <input type="text" class="form-control" id="exampleInputEmail1"
                                               aria-describedby="emailHelp" name="id" placeholder="ví dụ : 8396857873">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Số tiền</label>
                                        <input type="number" class="form-control" name="coin" id="exampleInputPassword1"
                                               placeholder="Nhập số tiền">
                                        <small style="color: red">Không tính khuyến mãi</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">username</label>
                                        <input type="text" class="form-control" name="username" id="exampleInputPassword1"
                                               placeholder="Nhập số tiền">
                                    </div>
                                </div>
                                <div class="box-footer pt-4">
                                    <button id="btn_buy_order" type="submit"
                                            class="btn rounded-10 btn-primary font-weight-bold w-100">Cập nhật
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
