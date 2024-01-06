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
                                                   class="form-control rounded-10" required
                                                   placeholder="Nhập ID hoặc Link tùy theo gói">
                                        </div>
                                        <div class="alert alert-info rounded-10 shadow-lg border-0 mt-3">
                                            Get ID Facebook từ Link nhanh tại
                                            <a class="fw-bold" target="_blank"
                                               href="https://findids.net">Đây</a>
                                            <a class="fw-bold" target="_blank"
                                               href="https://www.youtube.com/watch?v=n1Pv2Xel6Wo">[ Video hướng dẫn
                                                ]</a>
                                        </div>
                                        @include('Libs.form_package_list',['package'=>$package])
                                        @include('Libs.form_quantity')
                                        {{--                                        <div class="card bg-custom-6 border-0 rounded-10 mt-4 text-center mb-3">--}}
                                        {{--                                            <div class="card-body py-3">--}}
                                        {{--                                                <p class="mb-0  fw-bold">Tổng tiền của gói = 20000 </p>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}
                                        <div class="mb-3 border_package" id="group_reaction" style="display: none">
                                            <div class="form-group p-3">
                                                <label class="mt-2 font-medium">Chọn cảm xúc :</label>
                                                <div class="text-left mt-3">
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio0">
                                                            <input class="form-check-input checkbox d-none" type="radio"
                                                                   data-prices="101" id="inlineRadio0"
                                                                   name="object_type" value="like" checked="">
                                                            <img src="/assets/images/fb-reaction/like.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio1">
                                                            <input class="form-check-input checkbox d-none" type="radio"
                                                                   data-prices="100" id="inlineRadio1"
                                                                   name="object_type" value="love">
                                                            <img src="/assets/images/fb-reaction/love.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio2">
                                                            <input class="form-check-input checkbox d-none" type="radio"
                                                                   data-prices="100" id="inlineRadio2"
                                                                   name="object_type" value="care">
                                                            <img src="/assets/images/fb-reaction/care.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio3">
                                                            <input class="form-check-input checkbox d-none" type="radio"
                                                                   data-prices="100" id="inlineRadio3"
                                                                   name="object_type" value="haha">
                                                            <img src="/assets/images/fb-reaction/haha.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio4">
                                                            <input class="form-check-input checkbox d-none" type="radio"
                                                                   data-prices="100" id="inlineRadio4"
                                                                   name="object_type" value="wow">
                                                            <img src="/assets/images/fb-reaction/wow.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio6">
                                                            <input class="form-check-input checkbox d-none" type="radio"
                                                                   data-prices="100" id="inlineRadio6"
                                                                   name="object_type" value="sad">
                                                            <img src="/assets/images/fb-reaction/sad.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio7">
                                                            <input class="form-check-input checkbox d-none" type="radio"
                                                                   data-prices="100" id="inlineRadio7"
                                                                   name="object_type" value="angry">
                                                            <img src="/assets/images/fb-reaction/angry.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                </div>
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
            if (package_name === 'facebook_like_v3' || package_name === 'facebook_like_v19' || package_name === 'facebook_like_v7' || package_name === 'facebook_like_v9' || package_name === 'facebook_like_v2' || package_name === 'facebook_like_v12' || package_name === 'facebook_like_v16') {
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


    </script>
@endsection
