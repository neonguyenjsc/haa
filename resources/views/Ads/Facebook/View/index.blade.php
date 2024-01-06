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
                                            <label class="fw-bold form-label">Nhập ID hoặc Link tùy theo gói:</label>
                                            <input name="object_id" id="object_id" type="text"
                                                   class="form-control rounded-10" required onchange=""
                                                   placeholder="Nhập ID hoặc Link tùy theo gói">
                                        </div>
                                        @include('Libs.form_package_list',['package'=>$package])
                                        <div id="group_quantity" style="display: block">

                                            @include('Libs.form_quantity')
                                        </div>
                                        {{--                                        <div class="card bg-custom-6 border-0 rounded-10 mt-4 text-center mb-3">--}}
                                        {{--                                            <div class="card-body py-3">--}}
                                        {{--                                                <p class="mb-0  fw-bold">Tổng tiền của gói = 20000 </p>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}
                                        <div id="form_token" style="display: none">
                                            <div class="form-group mt-3">
                                                <label for="inputAddress2" class="form-label fw-bold">Token: </label>
                                                {{--                                            <span class="badge bg-custom-7 mb-2 text-white py-2 ">Số lượng: <span--}}
                                                {{--                                                    class="badge bg-custom-8" id="sl_txt">0</span></span>--}}
                                                <input type="text" name="token" class="form-control">
                                            </div>
                                            <div class="form-group mt-3">
                                                <label for="inputAddress2" class="form-label fw-bold">COOKIE: </label>
                                                {{--                                            <span class="badge bg-custom-7 mb-2 text-white py-2 ">Số lượng: <span--}}
                                                {{--                                                    class="badge bg-custom-8" id="sl_txt">0</span></span>--}}
                                                <input type="text" name="cookie_" class="form-control">
                                            </div>
                                        </div>
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
                                                    vnđ / tương tác</p>
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

        type_menu = 'video';
        $(document).ready(function () {
            // $('input').keyup(function () {
            //     checkOutCoin();
            // });
            $('input').change(function () {
                checkOutCoin();
            });
            $('textarea').keyup(function () {
                checkOutCoin();
            });
        });

        function checkOutCoin() {

            var quantity = parseFloat(getDataInput('quantity'));
            let package_name = getValueRadioByName('package_name');
            if (package_name !== 'facebook_view_21') {
                convertUid();
            }
            if (package_name === 'facebook_view_14' || package_name === 'facebook_view_15' || package_name === 'facebook_view_16' || package_name === 'facebook_view_17'|| package_name === 'facebook_view_22'|| package_name === 'facebook_view_23'|| package_name === 'facebook_view_24'|| package_name === 'facebook_view_25') {
                quantity = 1;
                $('#group_quantity').css('display', 'none');
                $('#form_token').css('display', 'block');
            } else {
                $('#group_quantity').css('display', 'block');
                $('#form_token').css('display', 'none');
            }
            var price_per = parseFloat(getDataAttRadio('package_name', 'price'));
            let check_out_coin = price_per * quantity;
            addTextId('check_out_coin', formatNumber(check_out_coin));
            addTextId('txt_quantity', formatNumber(quantity));
            addTextId('txt_price_per', formatNumber(price_per));
        }

        function buy() {
            let txt_quantity = $('#txt_quantity').text();
            let txt_price_per = $('#txt_price_per').text();
            let check_out_coin = $('#check_out_coin').text();
            modalSubmitForm('Bạn sẽ mua ' + txt_quantity + ' tương tác với giá ' + txt_price_per + '{{getCurrency()}} . Tổng tiền ' + check_out_coin, 'form_order');
        }


    </script>
@endsection
