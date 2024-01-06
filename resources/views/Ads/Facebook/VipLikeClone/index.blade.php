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
                                                   class="form-control rounded-10" required onchange="convertUid()"
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
                                                <select name="quantity" id="quantity" class="form-control rounded-10">
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                    <option value="150">150</option>
                                                    <option value="200">200</option>
                                                    <option value="250">250</option>
                                                    <option value="300">300</option>
                                                    <option value="500">500</option>
                                                    <option value="750">750</option>
                                                    <option value="1000">1000</option>
                                                    <option value="1500">1500</option>
                                                    <option value="2000">2000</option>
                                                    <option value="3000">3000</option>
                                                    <option value="5000">5000</option>
                                                    <option value="75000">75000</option>
                                                    <option value="100000">100000</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6" id="day_select">
                                                <label class="fw-bold form-label">Số ngày cần mua VIP</label>
                                                <select id="num_day" class="form-select rounded-10" name="num_day"
                                                        required="">
                                                    <option value="7">7 Ngày</option>
                                                    <option value="30">30 Ngày</option>
                                                    <option value="60">60 Ngày</option>
                                                    <option value="90">90 Ngày</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6" id="day_input" style="display: none">
                                                <label class="fw-bold form-label">Số ngày cần mua VIP</label>
                                                <input name="num_day_input" id="num_day_input" value="3" class="form-control" min="3">
                                            </div>
                                            <div class="col-md-6" id="group_slbv" style="display:block;">
                                                <label class="fw-bold form-label">Số lượng bài</label>
                                                <select class="form-control" id="slbv" name="slbv">
                                                    <option value="5">5 (giá x1)</option>
                                                    <option value="10">10 (giá x2)</option>
                                                    <option value="15">15 (giá x3)</option>
                                                    <option value="20">20 (giá x4)</option>
                                                    <option value="25">25 (giá x5)</option>
                                                    <option value="30">30 (giá x6)</option>
                                                    <option value="35">35 (giá x7)</option>
                                                    <option value="40">40 (giá x8)</option>
                                                    <option value="50">50 (giá x10)</option>
                                                    <option value="55">55 (giá x11)</option>
                                                    <option value="60">60 (giá x12)</option>
                                                    <option value="65">65 (giá x13)</option>
                                                    <option value="70">70 (giá x14)</option>
                                                    <option value="75">75 (giá x15)</option>
                                                    <option value="80">80 (giá x16)</option>
                                                    <option value="85">85 (giá x17)</option>
                                                    <option value="90">90 (giá x18)</option>
                                                    <option value="95">95 (giá x19)</option>
                                                    <option value="100">100 (giá x20)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 border_package" id="group_reaction" style="display: none">
                                            <div class="form-group p-3">
                                                <label class="mt-2 font-medium">Chọn cảm xúc :</label>
                                                <div class="text-left mt-3">
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio0">
                                                            <input class="form-check-input checkbox d-none"
                                                                   type="checkbox"
                                                                   data-prices="101" id="inlineRadio0"
                                                                   name="object_type[]" value="like" checked="">
                                                            <img src="/assets/images/fb-reaction/like.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio1">
                                                            <input class="form-check-input checkbox d-none"
                                                                   type="checkbox"
                                                                   data-prices="100" id="inlineRadio1"
                                                                   name="object_type[]" value="love">
                                                            <img src="/assets/images/fb-reaction/love.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio2">
                                                            <input class="form-check-input checkbox d-none"
                                                                   type="checkbox"
                                                                   data-prices="100" id="inlineRadio2"
                                                                   name="object_type[]" value="care">
                                                            <img src="/assets/images/fb-reaction/care.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio3">
                                                            <input class="form-check-input checkbox d-none"
                                                                   type="checkbox"
                                                                   data-prices="100" id="inlineRadio3"
                                                                   name="object_type[]" value="haha">
                                                            <img src="/assets/images/fb-reaction/haha.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio4">
                                                            <input class="form-check-input checkbox d-none"
                                                                   type="checkbox"
                                                                   data-prices="100" id="inlineRadio4"
                                                                   name="object_type[]" value="wow">
                                                            <img src="/assets/images/fb-reaction/wow.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio6">
                                                            <input class="form-check-input checkbox d-none"
                                                                   type="checkbox"
                                                                   data-prices="100" id="inlineRadio6"
                                                                   name="object_type[]" value="sad">
                                                            <img src="/assets/images/fb-reaction/sad.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio7">
                                                            <input class="form-check-input checkbox d-none"
                                                                   type="checkbox"
                                                                   data-prices="100" id="inlineRadio7"
                                                                   name="object_type[]" value="angry">
                                                            <img src="/assets/images/fb-reaction/angry.png" alt="image"
                                                                 class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                </div>
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
        type_menu = 'follow';
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

            let package_name = getValueRadioByName('package_name');
            let slb = 1;
            // console.log(package_name);
            if (package_name === 'facebook_vip_clone_sale_v2' || package_name === 'facebook_vip_clone_sale_v4' || package_name === 'facebook_vip_clone_sale_v8'|| package_name === 'facebook_vip_clone_sale_v3') {
                $('#group_slbv').css('display', 'block');
                slb = parseFloat(getValueSelectByName('slbv') / 5);
            } else {
                $('#group_slbv').css('display', 'none');
            }
            if (package_name === 'facebook_vip_clone_sale_v9') {
                $('#group_reaction').css('display', 'block');
            } else {
                $('#group_reaction').css('display', 'none');
            }
            let num_day = 0;
            num_day = parseFloat(getValueSelectByName('num_day'));
            if (package_name === 'facebook_vip_clone_sale_v4') {
                $('#day_input').css('display', 'block');
                $('#day_select').css('display', 'none');
                $('#num_day').attr("name","zzz");
                $('#num_day_input').attr("name","num_day");
                num_day = parseFloat($('input[name="num_day"]').val());
            } else {
                $('#day_input').css('display', 'none');
                $('#day_select').css('display', 'block');
                $('#num_day').attr("name","num_day");
                $('#num_day_input').attr("name","zzz");
                num_day = parseFloat(getValueSelectByName('num_day'));
            }
            console.log(slb);
            var price_per = parseFloat(getDataAttRadio('package_name', 'price'));
            var quantity = parseFloat(getValueSelectByName('quantity'));
            let check_out_coin = price_per * quantity * num_day * slb;
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
