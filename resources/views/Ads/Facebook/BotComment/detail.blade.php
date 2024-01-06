@extends('index')
@section('content')
    @include('Libs.form_modal')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">

                    {{--                    @include('Libs.form_tab_order')--}}
                    <div class="tab-content py-3">
                        <h4>Cập nhật {{$menu->name}}</h4>
                        <div class="tab-pane fade show active" id="successhome" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <form action="/facebook-bot-comment/update" method="post" id="form_order">
                                        @include('Libs.success_message')
                                        <div class="tab-pane fade fade-left show active"
                                             id="btabs-animated-slideleft-home" role="tabpanel">
                                            <div class="form-group row"><label class="col-sm-4 col-form-label" for="">Cookie
                                                    tài khoản facebook cần
                                                    chạy:</label>
                                                <div class="col-sm-8"><input type="text" onchange="checkCookie()"
                                                                             class="form-control"
                                                                             id="ctkfcc" name="ctkfcc" placeholder="">
                                                </div>
                                            </div>
                                            <div class="form-group row mt-4">
                                                <label class="col-sm-4 fw-bold form-label">Thông tin tài khoản</label>
                                                <div class="col-sm-8">
                                                    <input name="usernamefb" id="fb_name" type="text"
                                                           class="form-control rounded-10" required
                                                           placeholder="" disabled>
                                                    <input name="id_order" id="id_order" type="hidden"
                                                           value="{{$data->id}}"
                                                           class="form-control rounded-10"
                                                           required
                                                           placeholder="">
                                                    <input name="idfb" id="idfb" type="text"
                                                           class="form-control rounded-10" required
                                                           placeholder="" disabled>
                                                </div>
                                            </div>
                                            <div class="form-group row mt-4">
                                                <label class="col-sm-4 col-form-label"
                                                       for="">Tương tác với:</label>
                                                <div class="col-sm-8">
                                                    <div class="card card-orange mt-2"><select
                                                            class="form-control custom-select select-light" id="ttv"
                                                            name="ttv">
                                                            <option value="FRIEND">Chỉ bài viết của bạn bè</option>
                                                            <option value="NEWFEED">Tất cả bài viết trên newfeed
                                                            </option>
                                                            <option value="FRIEND_GROUP">Chỉ bài viết bạn bè và
                                                                nhóm
                                                            </option>
                                                            <option value="LISTUIDPROFILE">Theo list ID profile</option>
                                                            <option value="LISTUIDNHOM">Theo list ID nhóm</option>
                                                        </select></div>
                                                </div>
                                            </div>
                                            <div class="form-group row mt-4" id="h_sex">
                                                <label class="col-sm-4 col-form-label" for="">Giới tính:</label>
                                                <div class="col-sm-8">
                                                    <div class="form-check">
                                                        <div
                                                            class="custom-control custom-radio custom-control-inline">
                                                            <input type="radio" class="custom-control-input"
                                                                   id="banbe_0" name="gioitinh" value="all"
                                                                   checked=""><label class="custom-control-label"
                                                                                     for="banbe_0">Tất cả</label>
                                                        </div>
                                                        <div
                                                            class="custom-control custom-radio custom-control-inline">
                                                            <input type="radio" class="custom-control-input"
                                                                   id="banbe_1" name="gioitinh" value="nam"><label
                                                                class="custom-control-label" for="banbe_1">Chỉ
                                                                nam</label></div>
                                                        <div
                                                            class="custom-control custom-radio custom-control-inline">
                                                            <input type="radio" class="custom-control-input"
                                                                   id="banbe_2" name="gioitinh" value="nu"><label
                                                                class="custom-control-label" for="banbe_2">Chỉ
                                                                nữ</label></div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <div
                                                        class="custom-control custom-checkbox custom-control-inline">
                                                        <label class="custom-control-label"
                                                               for="blbv">Bình luận bài
                                                            viết:</label></div>
                                                    <h6 class="mb-0 font-13">Nên chọn ngẫu nhiên từ 1 đến 5</h6>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="card card-gray">
                                                        <div class="card-body py-2">
                                                            <div class="form-group">
                                                                <div
                                                                    class="custom-control custom-checkbox custom-control-inline">
                                                                </div>
                                                            </div>
                                                            <div class="form-group"><span
                                                                    class="badge bg-custom-3 mb-2 mr-2"
                                                                    onclick="addComment(this)"
                                                                    style="font-size: 100%; cursor: pointer;">{icon}</span><span
                                                                    class="badge bg-custom-3 mb-2 mr-2"
                                                                    onclick="addComment(this)"
                                                                    style="font-size: 100%; cursor: pointer;">{name}</span><span
                                                                    class="badge bg-custom-3 mb-2 mr-2"
                                                                    onclick="addComment(this)"
                                                                    style="font-size: 100%; cursor: pointer;">{tag}</span><span
                                                                    class="badge bg-custom-3 mb-2 mr-2"
                                                                    onclick="addComment(this)"
                                                                    style="font-size: 100%; cursor: pointer;">{dongho}</span><span
                                                                    class="badge bg-custom-3 mb-2 mr-2"
                                                                    onclick="addComment(this)"
                                                                    style="font-size: 100%; cursor: pointer;">{anhrandom}</span><span
                                                                    class="badge bg-custom-3 mb-2 mr-2"
                                                                    onclick="addComment(this)"
                                                                    style="font-size: 100%; cursor: pointer;">{xinchao}</span><span
                                                                    class="badge bg-custom-3 mb-2 mr-2"
                                                                    onclick="addComment(this)"
                                                                    style="font-size: 100%; cursor: pointer;">{anhtuychinh}</span><span
                                                                    class="badge bg-custom-2 mb-2 mr-2"
                                                                    onclick="addComment(this)"
                                                                    style="font-size: 100%; cursor: pointer;">Dấu | nội dung mới</span><textarea
                                                                    rows="2" type="text" class="form-control"
                                                                    id="blbv_cmt" name="blbv_cmt"
                                                                    placeholder="Nhập nội dung muốn bot tự động bình luận bài viết mới nhất của bạn bè"></textarea>
                                                                <h6 class="mb-0 mt-2 text-muted">Nhập: {sticker} =
                                                                    để
                                                                    sử dụng sticker (nếu bạn không dùng
                                                                    'Sticker
                                                                    tùy chỉnh' chúng tôi sẽ để nó random các
                                                                    sticker có trên hệ thống)</h6><h6
                                                                    class="mb-0 mt-2 text-muted">Nhập:
                                                                    {icon1}{icon2}-&gt;{icon10}
                                                                    = random emoij</h6><h6
                                                                    class="mb-0 mt-2 text-muted">
                                                                    Nhập: {name} = tên facebook chủ post</h6><h6
                                                                    class="mb-0 mt-2 text-muted">Nhập: {tag} = tag
                                                                    chủ
                                                                    post vào comment</h6><h6
                                                                    class="mb-0 mt-2 text-muted">Nhập: {dongho} =
                                                                    lấy
                                                                    thời gian hiện tại</h6><h6
                                                                    class="mb-0 mt-2 text-muted">Nhập: {anhrandom} =
                                                                    nếu
                                                                    muốn random ảnh con HEO,CHUỘT kèm tên chủ bài
                                                                    viết</h6><h6 class="mb-0 mt-2 text-muted">Nhập:
                                                                    {xinchao} = nếu muốn random ảnh con HEO,CHUỘT
                                                                    kèm
                                                                    Xin Chào! tên chủ bài viết</h6><h6
                                                                    class="mb-0 mt-2 text-muted">Lưu ý: Nếu bạn muốn
                                                                    chạy nhiều nội dung khác nhau thì mỗi nội dung
                                                                    cách
                                                                    nhau dấu <span class="text-danger"> | </span> Ví
                                                                    dụ:
                                                                    <span class="text-danger"> nội dung cmt 1|nội dung cmt 2|{sticker} </span>
                                                                </h6></div>
                                                            <div class="form-group" style="margin-bottom: 0px;">
                                                                <div class="row align-items-center">
                                                                    <div class="col-auto"><h6 class="mb-0">Tối đa 1
                                                                            ngày:</h6></div>
                                                                    <div class="col-4">
                                                                        <div class="input-group"><input
                                                                                type="number"
                                                                                id="blbv_tdmn"
                                                                                name="blbv_tdmn"
                                                                                class="form-control input-light"
                                                                                value="100">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col text-left"><h6 class="mb-0">Bình
                                                                            luận</h6></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="h_group_id">
                                                <div class="col-sm-4 ">
                                                    <label
                                                        class="col-form-label" for="">Danh sách id nhóm:</label>
                                                </div>
                                                <div class="col-sm-8"><textarea rows="2" name="listid" id="listid"
                                                                                placeholder="Nhập list ID Profile bạn muốn chạy BOT tương tác, ngăn cách nhau bởi dấu , (Vd : 100047535830919,100047535830919)"
                                                                                class="form-control input-gray"></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"
                                                                                    for="">Bài viết/phút:</label>
                                                <div class="col-sm-8">
                                                    <div class="card card-orange mt-2"><select
                                                            class="form-control custom-select select-light"
                                                            id="bvtp"
                                                            name="bvtp">
                                                            <option value="1">Tương tác 1 bài viết mỗi 1 đến 15
                                                                phút
                                                            </option>
                                                            <option value="2">Tương tác 2 bài viết mỗi 1 đến 15
                                                                phút
                                                            </option>
                                                            <option value="3">Tương tác 3 bài viết mỗi 1 đến 15
                                                                phút
                                                            </option>
                                                            <option value="4">Tương tác 4 bài viết mỗi 1 đến 15
                                                                phút
                                                            </option>
                                                            <option value="5">Tương tác 5 bài viết mỗi 1 đến 15
                                                                phút
                                                            </option>
                                                            <option value="10">Tương tác 10 bài viết mỗi 1 đến 15
                                                                phút
                                                            </option>
                                                        </select></div>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <div
                                                        class="custom-control custom-checkbox custom-control-inline">
                                                        <label
                                                            class="custom-control-label" for="lnncx">Like ngẫu nhiên
                                                            cảm
                                                            xúc:</label></div>
                                                    <h6 class="mb-0 font-13 text-danger">Có thể chọn nhiều loại cảm
                                                        xúc</h6></div>
                                                <div class="col-md-8">
                                                    <div class="card card-gray">
                                                        <div class="card-body py-2">
                                                            <div class="text-left mt-3">
                                                                <div class="form-check form-check-inline">
                                                                    <label class="form-check-label "
                                                                           for="inlineRadio0">
                                                                        <input
                                                                            class="form-check-input checkbox d-none"
                                                                            type="checkbox" data-prices="101"
                                                                            id="inlineRadio0" name="lnncx_type[]"
                                                                            value="like" checked="">
                                                                        <img
                                                                            src="/assets/images/fb-reaction/like.png"
                                                                            alt="image"
                                                                            class="d-block ml-2 rounded-circle"
                                                                            width="50">
                                                                    </label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <label class="form-check-label "
                                                                           for="inlineRadio1">
                                                                        <input
                                                                            class="form-check-input checkbox d-none"
                                                                            type="checkbox"
                                                                            id="inlineRadio1" name="lnncx_type[]"
                                                                            value="love">
                                                                        <img
                                                                            src="/assets/images/fb-reaction/love.png"
                                                                            alt="image"
                                                                            class="d-block ml-2 rounded-circle"
                                                                            width="50">
                                                                    </label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <label class="form-check-label "
                                                                           for="inlineRadio2">
                                                                        <input
                                                                            class="form-check-input checkbox d-none"
                                                                            type="checkbox"
                                                                            id="inlineRadio2" name="lnncx_type[]"
                                                                            value="care">
                                                                        <img
                                                                            src="/assets/images/fb-reaction/care.png"
                                                                            alt="image"
                                                                            class="d-block ml-2 rounded-circle"
                                                                            width="50">
                                                                    </label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <label class="form-check-label "
                                                                           for="inlineRadio3">
                                                                        <input
                                                                            class="form-check-input checkbox d-none"
                                                                            type="checkbox"
                                                                            id="inlineRadio3" name="lnncx_type[]"
                                                                            value="haha">
                                                                        <img
                                                                            src="/assets/images/fb-reaction/haha.png"
                                                                            alt="image"
                                                                            class="d-block ml-2 rounded-circle"
                                                                            width="50">
                                                                    </label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <label class="form-check-label "
                                                                           for="inlineRadio4">
                                                                        <input
                                                                            class="form-check-input checkbox d-none"
                                                                            type="checkbox"
                                                                            id="inlineRadio4" name="lnncx_type[]"
                                                                            value="wow">
                                                                        <img
                                                                            src="/assets/images/fb-reaction/wow.png"
                                                                            alt="image"
                                                                            class="d-block ml-2 rounded-circle"
                                                                            width="50">
                                                                    </label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <label class="form-check-label "
                                                                           for="inlineRadio6">
                                                                        <input
                                                                            class="form-check-input checkbox d-none"
                                                                            type="checkbox"
                                                                            id="inlineRadio6" name="lnncx_type[]"
                                                                            value="sad">
                                                                        <img
                                                                            src="/assets/images/fb-reaction/sad.png"
                                                                            alt="image"
                                                                            class="d-block ml-2 rounded-circle"
                                                                            width="50">
                                                                    </label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <label class="form-check-label "
                                                                           for="inlineRadio7">
                                                                        <input
                                                                            class="form-check-input checkbox d-none"
                                                                            type="checkbox"
                                                                            id="inlineRadio7" name="lnncx_type[]"
                                                                            value="angry">
                                                                        <img
                                                                            src="/assets/images/fb-reaction/angry.png"
                                                                            alt="image"
                                                                            class="d-block ml-2 rounded-circle"
                                                                            width="50">
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group" style="margin-bottom: 0px;">
                                                                <div class="row align-items-center">
                                                                    <div class="col-auto"><h6 class="mb-0">Tối đa 1
                                                                            ngày:</h6></div>
                                                                    <div class="col-4">
                                                                        <div class="input-group"><input
                                                                                type="number"
                                                                                id="lnncx_tdmn"
                                                                                name="lnncx_tdmn"
                                                                                class="form-control input-light"
                                                                                value="200">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col text-left"><h6 class="mb-0">Cảm
                                                                            xúc</h6></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"
                                                                                    for="">Thời gian chạy tương
                                                    tác:</label>
                                                <div class="col-sm-8">
                                                    <div class="row align-items-center">
                                                        <div class="col-auto"><h6 class="bold mb-0">Từ:</h6></div>
                                                        <div class="col"><select required=""
                                                                                 class="select-gray custom-select form-control"
                                                                                 id="tgctt_tu" name="tgctt_tu">
                                                                <option value="0">0</option>
                                                                <option value="1">1</option>
                                                                <option value="2">2</option>
                                                                <option value="3">3</option>
                                                                <option value="4">4</option>
                                                                <option value="5">5</option>
                                                                <option value="6">6</option>
                                                                <option value="7">7</option>
                                                                <option value="8">8</option>
                                                                <option value="9">9</option>
                                                                <option value="10">10</option>
                                                                <option value="11">11</option>
                                                                <option value="12">12</option>
                                                                <option value="13">13</option>
                                                                <option value="14">14</option>
                                                                <option value="15">15</option>
                                                                <option value="16">16</option>
                                                                <option value="17">17</option>
                                                                <option value="18">18</option>
                                                                <option value="19">19</option>
                                                                <option value="20">20</option>
                                                                <option value="21">21</option>
                                                                <option value="22">22</option>
                                                                <option value="23">23</option>
                                                            </select></div>
                                                        <div class="col-auto px-0"><h6 class="bold mb-0">Giờ</h6>
                                                        </div>
                                                        <div class="col-auto"><h6 class="bold mb-0">đến</h6></div>
                                                        <div class="col"><select required=""
                                                                                 class="select-gray custom-select form-control"
                                                                                 id="tgctt_den" name="tgctt_den">
                                                                <option value="0">0</option>
                                                                <option value="1">1</option>
                                                                <option value="2">2</option>
                                                                <option value="3">3</option>
                                                                <option value="4">4</option>
                                                                <option value="5">5</option>
                                                                <option value="6">6</option>
                                                                <option value="7">7</option>
                                                                <option value="8">8</option>
                                                                <option value="9" selected>9</option>
                                                                <option value="10">10</option>
                                                                <option value="11">11</option>
                                                                <option value="12">12</option>
                                                                <option value="13">13</option>
                                                                <option value="14">14</option>
                                                                <option value="15">15</option>
                                                                <option value="16">16</option>
                                                                <option value="17">17</option>
                                                                <option value="18">18</option>
                                                                <option value="19">19</option>
                                                                <option value="20">20</option>
                                                                <option value="21">21</option>
                                                                <option value="22">22</option>
                                                                <option value="23">23</option>
                                                            </select></div>
                                                        <div class="col-auto pl-0"><h6 class="bold mb-0">Giờ</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"
                                                                                    for="">Số ngày mua chạy Auto
                                                    tương
                                                    tác:</label>
                                                <div class="col-sm-8">
                                                    <div class="card card-orange mt-2"><select onchange="checkOutCoin()"
                                                                                               class="form-control custom-select select-light"
                                                                                               id="snmcatt"
                                                                                               name="snmcatt">
                                                            <option value="10">10</option>
                                                            <option value="30">30</option>
                                                            <option value="60">60</option>
                                                            <option value="90">90</option>
                                                        </select></div>
                                                </div>
                                            </div>
                                            <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"
                                                                                    for="">Black List Từ
                                                    Khóa:</label>
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <div class="form-group">
                                                            <textarea rows="2" type="text"
                                                                      class="form-control"
                                                                      id="blacklisttukhoa"
                                                                      name="blacklisttukhoa"
                                                                      placeholder="Nhập list từ khóa có chứa trong bài viết mà bạn không muốn BOT chạy tương tác, ngăn cách nhau bởi dấu , (Vd : buồn, đám tang, chia buồn)"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"
                                                                                    for="">Black List ID:</label>
                                                <div class="col-sm-8">
                                                    <div class="form-group">
                                                        <div class="form-group">
                                                            <textarea rows="2" type="text"
                                                                      class="form-control"
                                                                      id="blacklistid"
                                                                      name="blacklistid"
                                                                      placeholder="Nhập list ID bạn không muốn BOT chạy tương tác, ngăn cách nhau bởi dấu , (Vd : 100047535830919,100047535830919)"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-4"><label class="mt-2"></label></div>
                                                @include('Libs.form_notes')
                                            </div>
                                            <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"
                                                                                    for="">Proxy:</label>
                                                <div class="col-sm-8">
                                                    <div class="card card-orange mt-2"><select
                                                            class="form-control custom-select select-light"
                                                            id="id_proxy"
                                                            name="id_proxy">
                                                            <option value="1" data-port=""
                                                                    data-username=""
                                                                    data-password="">--
                                                            </option>
                                                            @foreach($proxy as $item)
                                                                <option value="{{$item->orders_id}}"
                                                                        data-port="{{$item->port}}"
                                                                        data-username="{{$item->proxy_username}}"
                                                                        data-password="{{$item->proxy_password}}">{{$item->ip}}
                                                                </option>
                                                            @endforeach

                                                        </select></div>
                                                    <div class="alert alert-danger" style="margin-top: 5px;"><i
                                                            class="fa fa-exclamation-triangle"></i> <strong>Lưu
                                                            ý:</strong> Nên mua IP riêng để sử dụng BOT Tương Tác
                                                        không
                                                        bị checkpoint, 1 IP chỉ nên dùng cho 1
                                                        -&gt; 3 tài khoản Facebook!<br>Mua proxy: <a
                                                            href="/facebook-proxy" target="_blank"
                                                            class="font-bold">
                                                            Tại đây</a></div>
                                                </div>
                                            </div>
                                            <div class="form-group row"><label class="col-sm-4 col-form-label"
                                                                               for="">Ghi
                                                    chú:</label>
                                                <div class="col-sm-8"><textarea rows="2"
                                                                                placeholder="Nhập nội dung ghi chú về tiến trình của bạn"
                                                                                class="form-control input-gray"
                                                                                id="gc"
                                                                                name="gc"></textarea></div>
                                            </div>
                                            <button type="button" onclick="buy()"
                                                    class="btn mb-3 rounded-10 btn-dark fw-bold w-100">Mua
                                                dịch vụ
                                            </button>
                                        </div>

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

        function buy() {
            let txt_quantity = $('#txt_quantity').text();
            let txt_price_per = $('#txt_price_per').text();
            let check_out_coin = $('#check_out_coin').text();
            modalSubmitForm('Bạn sẽ mua ' + txt_quantity + ' tương tác với giá ' + txt_price_per + '{{getCurrency()}} . Tổng tiền ' + check_out_coin, 'form_order');
        }

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
            let price_per = parseFloat(getDataAttRadio('package_name', 'price'));
            let days = parseFloat(getValueSelectByName('snmcatt'));
            let check_out_coin = price_per * days;
            addTextId('check_out_coin', formatNumber(check_out_coin));
            addTextId('txt_quantity', days);
            addTextId('txt_price_per', formatNumber(price_per));
        }

        function checkBlnn() {
            if ($('#blbv:checked').val()) {
                $('#display_on_blnn').attr("style", "display:block");
                $('#display_of_blnn').attr("style", "display:none");
            } else {
                $('#display_on_blnn').attr("style", "display:none");
                $('#display_of_blnn').attr("style", "display:block");
            }
        }

        function addComment(e) {
            //a.innerText
            let ele = $('#blbv_cmt');
            let t = ele.val();
            let str = e.innerText;
            if (str === '{icon}') {
                str = '{icon' + (Math.floor(Math.random() * 10) + 1) + '}'
            }
            if (str === 'Dấu | nội dung mới') {
                str = '|';
            }
            str = t + str;
            ele.val(str);
        }

        $('select[name="ttv"]').change(function () {
            $('#h_sex').attr("style", "display:none");
            $('#h_group_id').attr("style", "display:none");
            if ($(this).val() === 'FRIEND') {
                $('#h_sex').attr("style", "display:-webkit-box");
                $('#h_group_id').attr("style", "display:none");
            }
            if ($(this).val() === 'LISTUIDPROFILE' || $(this).val() === 'LISTUIDNHOM') {
                $('#h_sex').attr("style", "display:none");
                $('#h_group_id').attr("style", "display:-webkit-box");
            }
        });

        function checkCookie() {
            let cookie = getDataInput('ctkfcc');
            $.ajax({
                url: '/api/check-cookie',
                type: 'post',
                data: {cookie: cookie},
                success: function (data) {
                    $('#fb_name').val(data.data.name);
                    $('#idfb').val(data.data.uid);
                }
            });
            checkOutCoin();
        }
    </script>
@endsection
