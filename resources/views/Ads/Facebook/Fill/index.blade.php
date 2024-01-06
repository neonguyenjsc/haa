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
                                            <label class="fw-bold form-label">Nhập cookie</label>
                                            <input onchange="checkCookie()" name="cookie" id="object_id" type="text"
                                                   class="form-control rounded-10" required
                                                   placeholder="Nhập cookie">

                                            <p>Tải tiện ích lọc bạn bè : <a
                                                    href="https://drive.google.com/file/d/1CvfYBldi9YDiWOe2kvCIyqO_kP3wed81/view?usp=sharing" target="_blank">Tại
                                                    đây</a></p>
                                            <p>Hướng dẫn cài tiện ích vào trình duyệt Chrome : <a
                                                    href="https://www.youtube.com/watch?v=e-qDLnjt06A"
                                                    target="_blank">Tại đây</a></p>
                                        </div>
                                        <div class="form-group">
                                            <label class="fw-bold form-label">Thông tin tài khoản</label>
                                            <input name="fb_name" id="fb_name" type="text"
                                                   class="form-control rounded-10" required
                                                   placeholder="" disabled>
                                        </div>
                                        @include('Libs.form_package_list',['package'=>$package])
                                        {{--                                        @include('Libs.form_quantity')--}}
                                        <div class="form-group mt-3" style="display: none">
                                            <label class="fw-bold form-label">Số lượng:</label>
                                            <input name="quantity" class="form-control rounded-10"
                                                   value="1"
                                                   type="number" min="100" placeholder="Số lượng" required>
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
        type_menu = 'follow';
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

            let package_name = getValueRadioByName('package_name');
            // console.log(package_name);
            if (package_name === 'facebook_like_v3') {
                $('#group_reaction').css('display', 'block');
            } else {
                $('#group_reaction').css('display', 'none');
            }

            var price_per = parseFloat(getDataAttRadio('package_name', 'price'));
            var quantity = parseFloat(getDataInput('quantity'));
            let check_out_coin = price_per * quantity;
            console.log(check_out_coin);
            console.log(price_per);
            console.log(quantity);
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


        function getUid() {
            loading();
            let link = getDataInput('object_id');
            $.ajax({
                url: "/api/facebook-mem-group/get-uid",
                headers: {
                    'api-key': '{{getInfoUser('api_key')}}',
                },
                type: "POST",
                data: {link: link},
                success: function (data) {
                    if (data.data.group_id) {
                        $('input[name="object_id"]').val(data.data.group_id);
                    }
                    stopLoading();
                    modalSuccess("Đã chuyển xong " + data.data.group_id);
                }
            });
        }

        function checkCookie() {
            let cookie = getDataInput('cookie');
            $.ajax({
                url: '/api/check-cookie',
                type: 'post',
                data: {cookie: cookie},
                success: function (data) {
                    $('#fb_name').val(data.data.name);
                }
            });
        }

    </script>
@endsection

