@extends('index')
@section('content')
    @include('Libs.form_modal')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">

                    @include('Libs.form_tab_order')
                    <div class="tab-content py-3">
                        <h4>{{$menu->name}}</h4>
                        <div class="tab-pane fade show active" id="successhome" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <form action="{{getUri()}}/buy" method="post" id="form_order">
                                        @include('Libs.success_message')
                                        <div class="form-group">
                                            <label class="fw-bold form-label">Nhập ID </label>
                                            <input name="object_id" id="object_id" type="text"
                                                   class="form-control rounded-10" required
                                                   placeholder="Nhập ID hoặc Link tùy theo gói">
                                        </div>
                                        <div class="alert alert-info rounded-10 shadow-lg border-0 mt-3">
                                            Get ID Facebook từ Link nhanh tại <a class="fw-bold" target="_blank"
                                                                                 href="https://findids.net">Findids.net</a>
                                        </div>
                                        @include('Libs.form_package_list',['package'=>$package])
                                        <div class="row form-group mt-3">
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label text-danger">Số lượng</label>
                                                <input type="number" class="form-control" name="quantity" min="50"
                                                       value="50">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label">Số ngày cần mua VIP</label>
                                                <select id="days" class="form-select rounded-10" name="days"
                                                        required="">
                                                    <option value="7">7 Ngày</option>
                                                    <option value="30">30 Ngày</option>
                                                    <option value="60">60 Ngày</option>
                                                    <option value="90">90 Ngày</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6" id="group_slbv" style="display:block;">
                                                <label class="fw-bold form-label">Số lượng bài</label>
                                                <input type="number" class="form-control" name="total_post" min="2"
                                                       value="1">
                                            </div>
                                        </div>
                                        {{--                                        <div class="card bg-custom-6 border-0 rounded-10 mt-4 text-center mb-3">--}}
                                        {{--                                            <div class="card-body py-3">--}}
                                        {{--                                                <p class="mb-0  fw-bold">Tổng tiền của gói = 20000 </p>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}
                                        @include('Libs.form_notes')
                                        <div class="form-group mt-3">
                                            <label for="inputAddress2" class="form-label fw-bold">Ghi chú: </label>
                                            {{--                                            <span class="badge bg-custom-7 mb-2 text-white py-2 ">Số lượng: <span--}}
                                            {{--                                                    class="badge bg-custom-8" id="sl_txt">0</span></span>--}}
                                            <textarea class="form-control rszn rounded-10" id="inputAddress2"
                                                      placeholder="Ghi chú" rows="3"></textarea>
                                        </div>
                                        <div class="form-group mt-3">
                                            <div
                                                class="alert alert-success border-0 alert-dismissible fade show text-center rounded-10"
                                                role="alert">
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                        aria-label="Close"></button>
                                                <h3><span id="check_out_coin">0</span> vnđ</h3>
                                                <strong>Tổng tiền thanh toán</strong>
                                                <p class="mb-0 mt-3 text-center">Bạn sẽ buff <span
                                                        id="txt_quantity">0</span> tương tác với giá <span
                                                        id="txt_price_per">0</span>
                                                    vnđ / tương tác trong
                                                    <span
                                                        id="txt_num_day">0</span> ngày</p>
                                            </div>
                                        </div>
                                        <button type="button" onclick="buy()"
                                                class="btn mb-3 rounded-10 btn-dark fw-bold w-100">Mua
                                            dịch vụ
                                        </button>
                                    </form>
                                </div>
                                @include('Libs.form_notes_right')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            // $('input').keyup(function () {
            //     checkOutCoin();
            // });
            $('input').change(function () {
                checkOutCoin();
            });
            $('select').change(function () {
                checkOutCoin();
            });
            $('textarea').keyup(function () {
                checkOutCoin();
            });
        });

        function checkOutCoin() {
            let slb = getDataInput('total_post');
            var price_per = parseFloat(getDataAttRadio('package_name', 'price'));
            var quantity = parseFloat(getDataInput('quantity'));
            var num_day = parseFloat(getValueSelectByName('days'));
            let check_out_coin = price_per * quantity * num_day * slb;
            // console.log(slb);
            // console.log(price_per);
            // console.log(quantity);
            // console.log(num_day);
            // console.log(slb);
            addTextId('check_out_coin', formatNumber(check_out_coin));
            addTextId('txt_quantity', formatNumber(quantity));
            addTextId('txt_price_per', formatNumber(price_per));
            addTextId('txt_num_day', formatNumber(num_day));
        }

        function buy() {
            let txt_quantity = $('#txt_quantity').text();
            let txt_price_per = $('#txt_price_per').text();
            let check_out_coin = $('#check_out_coin').text();
            let txt_num_day = $('#txt_num_day').text();
            modalSubmitForm('Bạn sẽ mua ' + txt_quantity + ' tương tác với giá ' + txt_price_per + '{{getCurrency()}} Trong ' + txt_num_day + ' ngày. Tổng tiền ' + check_out_coin, 'form_order');
        }


    </script>
@endsection
