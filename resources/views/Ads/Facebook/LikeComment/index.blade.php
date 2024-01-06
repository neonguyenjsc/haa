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
                                            <label class="fw-bold form-label">Nhập ID bình luận:</label>
                                            <input name="object_id" id="object_id" type="text"
                                                   class="form-control rounded-10" required onchange="checkOutCoin()"
                                                   placeholder="Nhập ID bình luận">
                                        </div>
{{--                                        <div class="alert alert-info rounded-10 shadow-lg border-0 mt-3">--}}
{{--                                            Get ID Facebook từ Link nhanh tại <a class="fw-bold" target="_blank"--}}
{{--                                                                                 href="https://findids.net">Findids.net</a>--}}
{{--                                        </div>--}}
                                        @include('Libs.form_package_list',['package'=>$package])
                                        @include('Libs.form_quantity')
                                        {{--                                        <div class="card bg-custom-6 border-0 rounded-10 mt-4 text-center mb-3">--}}
                                        {{--                                            <div class="card-body py-3">--}}
                                        {{--                                                <p class="mb-0  fw-bold">Tổng tiền của gói = 20000 </p>--}}
                                        {{--                                            </div>--}}
                                        {{--                                        </div>--}}
                                        <div class="mb-3 border_package" id="group_reaction" style="display: block">
                                            <div class="form-group p-3">
                                                <label class="mt-2 font-medium">Chọn cảm xúc :</label>
                                                <div class="text-left mt-3">
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio0">
                                                            <input class="form-check-input checkbox d-none" type="radio" data-prices="101" id="inlineRadio0" name="object_type" value="like" checked="">
                                                            <img src="/assets/images/fb-reaction/like.png" alt="image" class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio1">
                                                            <input class="form-check-input checkbox d-none" type="radio" data-prices="100" id="inlineRadio1" name="object_type" value="love">
                                                            <img src="/assets/images/fb-reaction/love.png" alt="image" class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio2">
                                                            <input class="form-check-input checkbox d-none" type="radio" data-prices="100" id="inlineRadio2" name="object_type" value="care">
                                                            <img src="/assets/images/fb-reaction/care.png" alt="image" class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio3">
                                                            <input class="form-check-input checkbox d-none" type="radio" data-prices="100" id="inlineRadio3" name="object_type" value="haha">
                                                            <img src="/assets/images/fb-reaction/haha.png" alt="image" class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio4">
                                                            <input class="form-check-input checkbox d-none" type="radio" data-prices="100" id="inlineRadio4" name="object_type" value="wow">
                                                            <img src="/assets/images/fb-reaction/wow.png" alt="image" class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio6">
                                                            <input class="form-check-input checkbox d-none" type="radio" data-prices="100" id="inlineRadio6" name="object_type" value="sad">
                                                            <img src="/assets/images/fb-reaction/sad.png" alt="image" class="d-block ml-2 rounded-circle" width="50">
                                                        </label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <label class="form-check-label " for="inlineRadio7">
                                                            <input class="form-check-input checkbox d-none" type="radio" data-prices="100" id="inlineRadio7" name="object_type" value="angry">
                                                            <img src="/assets/images/fb-reaction/angry.png" alt="image" class="d-block ml-2 rounded-circle" width="50">
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
        type_menu = 'like_comment';
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

        function getId_(id) {
            var result = null;
            var comment_id = id['match'](/(.*)comment_id=([0-9]{8,})/);
            if (comment_id) {
                var post_id = id['match'](/(.*)\/posts\/([0-9]{8,})/);
                var post_id2 = id['match'](/(.*)\/posts\/([0-9a-zA-Z]{8,})/);
                var photo_id = id['match'](/(.*)\/photo.php\?fbid=([0-9]{8,})/);
                var photo_id2 = id['match'](/(.*)\/?fbid=([0-9]{8,})/);
                var video_id = id['match'](/(.*)\/video.php\?v=([0-9]{8,})/);
                var story_id = id['match'](/(.*)\/story.php\?story_fbid=([0-9]{8,})/);
                var story_id2 = id['match'](/(.*)\/story.php\?story_fbid=([0-9a-zA-Z]{8,})/);
                var link_id = id['match'](/(.*)\/permalink.php\?story_fbid=([0-9]{8,})/);
                var link_id2 = id['match'](/(.*)\/permalink.php\?story_fbid=([0-9a-zA-Z]{8,})/);
                var other_id = id['match'](/(.*)\/([0-9]{8,})/);
                var comment_id = id['match'](/(.*)comment_id=([0-9]{8,})/);
                if (post_id) {
                    result = post_id[2]
                } else {
                    if (photo_id) {
                        result = photo_id[2]
                    } else {
                        if (video_id) {
                            result = video_id[2]
                        } else {
                            if (story_id) {
                                result = story_id[2]
                            } else {
                                if (link_id) {
                                    result = link_id[2]
                                } else {
                                    if (other_id) {
                                        result = other_id[2]
                                    } else {
                                        if (photo_id2) {
                                            result = photo_id2[2]
                                        } else {
                                            if (post_id2) {
                                                result = post_id2[2]
                                            } else {
                                                if (story_id2) {
                                                    result = story_id2[2]
                                                } else {
                                                    if (link_id2) {
                                                        result = link_id2[2]
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                ;result += '_' + comment_id[2]
            }
            ;
            if (result == null) {
                result = "Url không hợp lệ.";
            }
            return result;
        }

        function checkOutCoin() {
            let package_name = getValueRadioByName('package_name');
            if (package_name === 'facebook_like_comment') {
                convertUid()
            }
            if (package_name === 'facebook_like_comment_sv2') {
                $('input[name="object_id"]').val(getId_(getDataInput('object_id')));
            }
            $('#group_reaction').css('display', 'block');

            var price_per = parseFloat(getDataAttRadio('package_name', 'prices'));
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
