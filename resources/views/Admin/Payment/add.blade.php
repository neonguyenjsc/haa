@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content py-3">
                        <h4>Thêm ngân hàng</h4>
                        <div class="tab-pane fade show active">
                            <h6><a href="/admin/payment"><span
                                        class="badge bg-warning">Quay lại</span></a></h6>
                            @include('Libs.success_message')
                            <form action="/admin/payment/add" method="POST"
                                  id="order_form">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tên ngân hàng:</label>
                                        <input id="object_id" type="text" name="name"
                                               class="form-control rounded-10"
                                               value="" required
                                               placeholder="">
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Chủ thẻ:</label>
                                        <input id="object_id" type="text" name="full_name"
                                               class="form-control rounded-10"
                                               value="" required
                                               placeholder="">
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Chi nhánh:</label>
                                        <input id="object_id" type="text" name="branch"
                                               class="form-control rounded-10"
                                               value="" required
                                               placeholder="">
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Số tài khoản:</label>
                                        <input id="object_id" type="text" name="stk"
                                               class="form-control rounded-10"
                                               value="" required
                                               placeholder="">
                                    </div>

                                </div>
                                <div class="box-footer">
                                    <button id="btn_buy_order" type="submit"
                                            class="btn rounded-10 btn-primary font-weight-bold w-100">Cập
                                        nhật
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
