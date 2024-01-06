@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    @include('Libs.form_tab_history')
                    <div class="tab-content py-3">
                        <h4>{{$menu->name}}</h4>
                        <div class="tab-pane fade show active">
                            @include('Libs.search')
                            <div class="table-responsive mt-3">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Mã đơn</th>
                                        @if(isAdmin())
                                            <th>username</th>
                                        @endif
                                        <td>Cập nhật cookie</td>
                                        <th>username Đại lý</th>
                                        <th>Object id</th>
                                        <th>Giá</th>
                                        <th>Số ngày</th>
                                        <th>Tổng tiền</th>
                                        <th>Hạn sữ dụng</th>
                                        <th>Ngày tạo</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($data as $i=>$item)
                                        <tr>
                                            <td>{{$item->id}}</td>
                                            @if(isAdmin())
                                                <td>{{$item->username}}</td>
                                            @endif
                                            <td><a href="/facebook-bot-comment/detail/{{$item->id}}">Cập nhật cookie</a>
                                            </td>
                                            <td>{{$item->client_username}}</td>
                                            <td><a target="_blank"
                                                   href="https://facebook.com/{{$item->fb_id}}">{{$item->fb_id}}</a>
                                            </td>
                                            <td>{{$item->price_per}}</td>
                                            <td>{{$item->days}}</td>
                                            <td>{{$item->prices}}</td>
                                            <td>{{date('Y-m-d H:i:s',$item->time_end)}}</td>
                                            <td>{{$item->created_at}}</td>
                                            <td>{{$item->notes}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--    <div class="modal fade" id="modal_update_cookie" tabindex="-1" role="dialog"--}}
    {{--         aria-labelledby="exampleModalCenterTitle" aria-hidden="true">--}}
    {{--        <div class="modal-dialog modal-lg" role="document">--}}
    {{--            <div class="modal-content">--}}
    {{--                <div class="modal-header">--}}
    {{--                    <h5 class="modal-title" id="exampleModalLongTitle">Cập nhật bot tương tác</h5>--}}
    {{--                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">--}}
    {{--                        <span aria-hidden="true">&times;</span>--}}
    {{--                    </button>--}}
    {{--                </div>--}}
    {{--                <div class="modal-body">--}}
    {{--                    <form action="/facebook-bot-comment/update-cookie" method="post">--}}
    {{--                        <div class="form-group">--}}
    {{--                            <input type="text" id="id_cookie" style="display: none" name="id" class="form-control">--}}
    {{--                            <form action="{{getUri()}}/buy" method="post" id="form_order">--}}
    {{--                                @include('Libs.success_message')--}}
    {{--                                <div class="tab-pane fade fade-left show active"--}}
    {{--                                     id="btabs-animated-slideleft-home" role="tabpanel">--}}
    {{--                                    <div class="form-group row"><label class="col-sm-4 col-form-label" for="">Cookie--}}
    {{--                                            tài khoản facebook cần--}}
    {{--                                            chạy:</label>--}}
    {{--                                        <div class="col-sm-8"><input type="text" onchange="checkCookie()"--}}
    {{--                                                                     class="form-control"--}}
    {{--                                                                     id="ctkfcc" name="ctkfcc" placeholder="">--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group row mt-4">--}}
    {{--                                        <label class="col-sm-4 col-form-label"--}}
    {{--                                               for="">Tương tác với:</label>--}}
    {{--                                        <div class="col-sm-8">--}}
    {{--                                            <div class="card card-orange mt-2"><select--}}
    {{--                                                    class="form-control custom-select select-light" id="ttv"--}}
    {{--                                                    name="ttv">--}}
    {{--                                                    <option value="FRIEND">Chỉ bài viết của bạn bè</option>--}}
    {{--                                                    <option value="NEWFEED">Tất cả bài viết trên newfeed--}}
    {{--                                                    </option>--}}
    {{--                                                    <option value="FRIEND_GROUP">Chỉ bài viết bạn bè và--}}
    {{--                                                        nhóm--}}
    {{--                                                    </option>--}}
    {{--                                                    <option value="LISTUIDPROFILE">Theo list ID profile</option>--}}
    {{--                                                    <option value="LISTUIDNHOM">Theo list ID nhóm</option>--}}
    {{--                                                </select></div>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group row mt-4" id="h_sex">--}}
    {{--                                        <label class="col-sm-4 col-form-label" for="">Giới tính:</label>--}}
    {{--                                        <div class="col-sm-8">--}}
    {{--                                            <div class="form-check">--}}
    {{--                                                <div--}}
    {{--                                                    class="custom-control custom-radio custom-control-inline">--}}
    {{--                                                    <input type="radio" class="custom-control-input"--}}
    {{--                                                           id="banbe_0" name="gioitinh" value="all"--}}
    {{--                                                           checked=""><label class="custom-control-label"--}}
    {{--                                                                             for="banbe_0">Tất cả</label>--}}
    {{--                                                </div>--}}
    {{--                                                <div--}}
    {{--                                                    class="custom-control custom-radio custom-control-inline">--}}
    {{--                                                    <input type="radio" class="custom-control-input"--}}
    {{--                                                           id="banbe_1" name="gioitinh" value="nam"><label--}}
    {{--                                                        class="custom-control-label" for="banbe_1">Chỉ--}}
    {{--                                                        nam</label></div>--}}
    {{--                                                <div--}}
    {{--                                                    class="custom-control custom-radio custom-control-inline">--}}
    {{--                                                    <input type="radio" class="custom-control-input"--}}
    {{--                                                           id="banbe_2" name="gioitinh" value="nu"><label--}}
    {{--                                                        class="custom-control-label" for="banbe_2">Chỉ--}}
    {{--                                                        nữ</label></div>--}}

    {{--                                            </div>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group row pb-2" id="h_group_id">--}}
    {{--                                        <div class="col-sm-4 ">--}}
    {{--                                            <label--}}
    {{--                                                class="col-form-label" for="">Nội dung bình luận:</label>--}}
    {{--                                        </div>--}}
    {{--                                        <div class="col-sm-8"><textarea rows="2" name="blbv_cmt" id="blbv_cmt"--}}
    {{--                                                                        placeholder="Nội dung bình luận cách nhau dấu |"--}}
    {{--                                                                        class="form-control input-gray"></textarea>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group row" id="h_group_id">--}}
    {{--                                        <div class="col-sm-4 ">--}}
    {{--                                            <label--}}
    {{--                                                class="col-form-label" for="">Danh sách id nhóm:</label>--}}
    {{--                                        </div>--}}
    {{--                                        <div class="col-sm-8"><textarea rows="2" name="listid" id="listid"--}}
    {{--                                                                        placeholder="Nhập list ID Profile bạn muốn chạy BOT tương tác, ngăn cách nhau bởi dấu , (Vd : 100047535830919,100047535830919)"--}}
    {{--                                                                        class="form-control input-gray"></textarea>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"--}}
    {{--                                                                            for="">Bài viết/phút:</label>--}}
    {{--                                        <div class="col-sm-8">--}}
    {{--                                            <div class="card card-orange mt-2"><select--}}
    {{--                                                    class="form-control custom-select select-light"--}}
    {{--                                                    id="bvtp"--}}
    {{--                                                    name="bvtp">--}}
    {{--                                                    <option value="1">Tương tác 1 bài viết mỗi 1 đến 15--}}
    {{--                                                        phút--}}
    {{--                                                    </option>--}}
    {{--                                                    <option value="2">Tương tác 2 bài viết mỗi 1 đến 15--}}
    {{--                                                        phút--}}
    {{--                                                    </option>--}}
    {{--                                                    <option value="3">Tương tác 3 bài viết mỗi 1 đến 15--}}
    {{--                                                        phút--}}
    {{--                                                    </option>--}}
    {{--                                                    <option value="4">Tương tác 4 bài viết mỗi 1 đến 15--}}
    {{--                                                        phút--}}
    {{--                                                    </option>--}}
    {{--                                                    <option value="5">Tương tác 5 bài viết mỗi 1 đến 15--}}
    {{--                                                        phút--}}
    {{--                                                    </option>--}}
    {{--                                                    <option value="10">Tương tác 10 bài viết mỗi 1 đến 15--}}
    {{--                                                        phút--}}
    {{--                                                    </option>--}}
    {{--                                                </select></div>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="row mt-2">--}}
    {{--                                        <div class="col-md-4">--}}
    {{--                                            <div--}}
    {{--                                                class="custom-control custom-checkbox custom-control-inline">--}}
    {{--                                                <label--}}
    {{--                                                    class="custom-control-label" for="lnncx">Like ngẫu nhiên--}}
    {{--                                                    cảm--}}
    {{--                                                    xúc:</label></div>--}}
    {{--                                            <h6 class="mb-0 font-13 text-danger">Có thể chọn nhiều loại cảm--}}
    {{--                                                xúc</h6></div>--}}
    {{--                                        <div class="col-md-8">--}}
    {{--                                            <div class="card card-gray">--}}
    {{--                                                <div class="card-body py-2">--}}
    {{--                                                    <div class="text-left mt-3">--}}
    {{--                                                        <div class="form-check form-check-inline">--}}
    {{--                                                            <label class="form-check-label "--}}
    {{--                                                                   for="inlineRadio0">--}}
    {{--                                                                <input--}}
    {{--                                                                    class="form-check-input checkbox d-none"--}}
    {{--                                                                    type="checkbox" data-prices="101"--}}
    {{--                                                                    id="inlineRadio0" name="lnncx_type[]"--}}
    {{--                                                                    value="like" checked="">--}}
    {{--                                                                <img--}}
    {{--                                                                    src="/assets/images/fb-reaction/like.png"--}}
    {{--                                                                    alt="image"--}}
    {{--                                                                    class="d-block ml-2 rounded-circle"--}}
    {{--                                                                    width="50">--}}
    {{--                                                            </label>--}}
    {{--                                                        </div>--}}
    {{--                                                        <div class="form-check form-check-inline">--}}
    {{--                                                            <label class="form-check-label "--}}
    {{--                                                                   for="inlineRadio1">--}}
    {{--                                                                <input--}}
    {{--                                                                    class="form-check-input checkbox d-none"--}}
    {{--                                                                    type="checkbox"--}}
    {{--                                                                    id="inlineRadio1" name="lnncx_type[]"--}}
    {{--                                                                    value="love">--}}
    {{--                                                                <img--}}
    {{--                                                                    src="/assets/images/fb-reaction/love.png"--}}
    {{--                                                                    alt="image"--}}
    {{--                                                                    class="d-block ml-2 rounded-circle"--}}
    {{--                                                                    width="50">--}}
    {{--                                                            </label>--}}
    {{--                                                        </div>--}}
    {{--                                                        <div class="form-check form-check-inline">--}}
    {{--                                                            <label class="form-check-label "--}}
    {{--                                                                   for="inlineRadio2">--}}
    {{--                                                                <input--}}
    {{--                                                                    class="form-check-input checkbox d-none"--}}
    {{--                                                                    type="checkbox"--}}
    {{--                                                                    id="inlineRadio2" name="lnncx_type[]"--}}
    {{--                                                                    value="care">--}}
    {{--                                                                <img--}}
    {{--                                                                    src="/assets/images/fb-reaction/care.png"--}}
    {{--                                                                    alt="image"--}}
    {{--                                                                    class="d-block ml-2 rounded-circle"--}}
    {{--                                                                    width="50">--}}
    {{--                                                            </label>--}}
    {{--                                                        </div>--}}
    {{--                                                        <div class="form-check form-check-inline">--}}
    {{--                                                            <label class="form-check-label "--}}
    {{--                                                                   for="inlineRadio3">--}}
    {{--                                                                <input--}}
    {{--                                                                    class="form-check-input checkbox d-none"--}}
    {{--                                                                    type="checkbox"--}}
    {{--                                                                    id="inlineRadio3" name="lnncx_type[]"--}}
    {{--                                                                    value="haha">--}}
    {{--                                                                <img--}}
    {{--                                                                    src="/assets/images/fb-reaction/haha.png"--}}
    {{--                                                                    alt="image"--}}
    {{--                                                                    class="d-block ml-2 rounded-circle"--}}
    {{--                                                                    width="50">--}}
    {{--                                                            </label>--}}
    {{--                                                        </div>--}}
    {{--                                                        <div class="form-check form-check-inline">--}}
    {{--                                                            <label class="form-check-label "--}}
    {{--                                                                   for="inlineRadio4">--}}
    {{--                                                                <input--}}
    {{--                                                                    class="form-check-input checkbox d-none"--}}
    {{--                                                                    type="checkbox"--}}
    {{--                                                                    id="inlineRadio4" name="lnncx_type[]"--}}
    {{--                                                                    value="wow">--}}
    {{--                                                                <img--}}
    {{--                                                                    src="/assets/images/fb-reaction/wow.png"--}}
    {{--                                                                    alt="image"--}}
    {{--                                                                    class="d-block ml-2 rounded-circle"--}}
    {{--                                                                    width="50">--}}
    {{--                                                            </label>--}}
    {{--                                                        </div>--}}
    {{--                                                        <div class="form-check form-check-inline">--}}
    {{--                                                            <label class="form-check-label "--}}
    {{--                                                                   for="inlineRadio6">--}}
    {{--                                                                <input--}}
    {{--                                                                    class="form-check-input checkbox d-none"--}}
    {{--                                                                    type="checkbox"--}}
    {{--                                                                    id="inlineRadio6" name="lnncx_type[]"--}}
    {{--                                                                    value="sad">--}}
    {{--                                                                <img--}}
    {{--                                                                    src="/assets/images/fb-reaction/sad.png"--}}
    {{--                                                                    alt="image"--}}
    {{--                                                                    class="d-block ml-2 rounded-circle"--}}
    {{--                                                                    width="50">--}}
    {{--                                                            </label>--}}
    {{--                                                        </div>--}}
    {{--                                                        <div class="form-check form-check-inline">--}}
    {{--                                                            <label class="form-check-label "--}}
    {{--                                                                   for="inlineRadio7">--}}
    {{--                                                                <input--}}
    {{--                                                                    class="form-check-input checkbox d-none"--}}
    {{--                                                                    type="checkbox"--}}
    {{--                                                                    id="inlineRadio7" name="lnncx_type[]"--}}
    {{--                                                                    value="angry">--}}
    {{--                                                                <img--}}
    {{--                                                                    src="/assets/images/fb-reaction/angry.png"--}}
    {{--                                                                    alt="image"--}}
    {{--                                                                    class="d-block ml-2 rounded-circle"--}}
    {{--                                                                    width="50">--}}
    {{--                                                            </label>--}}
    {{--                                                        </div>--}}
    {{--                                                    </div>--}}
    {{--                                                    <div class="form-group" style="margin-bottom: 0px;">--}}
    {{--                                                        <div class="row align-items-center">--}}
    {{--                                                            <div class="col-auto"><h6 class="mb-0">Tối đa 1--}}
    {{--                                                                    ngày:</h6></div>--}}
    {{--                                                            <div class="col-4">--}}
    {{--                                                                <div class="input-group"><input--}}
    {{--                                                                        type="number"--}}
    {{--                                                                        id="lnncx_tdmn"--}}
    {{--                                                                        name="lnncx_tdmn"--}}
    {{--                                                                        class="form-control input-light"--}}
    {{--                                                                        value="200">--}}
    {{--                                                                </div>--}}
    {{--                                                            </div>--}}
    {{--                                                            <div class="col text-left"><h6 class="mb-0">Cảm--}}
    {{--                                                                    xúc</h6></div>--}}
    {{--                                                        </div>--}}
    {{--                                                    </div>--}}
    {{--                                                </div>--}}
    {{--                                            </div>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"--}}
    {{--                                                                            for="">Thời gian chạy tương--}}
    {{--                                            tác:</label>--}}
    {{--                                        <div class="col-sm-8">--}}
    {{--                                            <div class="row align-items-center">--}}
    {{--                                                <div class="col-auto"><h6 class="bold mb-0">Từ:</h6></div>--}}
    {{--                                                <div class="col"><select required=""--}}
    {{--                                                                         class="select-gray custom-select form-control"--}}
    {{--                                                                         id="tgctt_tu" name="tgctt_tu">--}}
    {{--                                                        <option value="0">0</option>--}}
    {{--                                                        <option value="1">1</option>--}}
    {{--                                                        <option value="2">2</option>--}}
    {{--                                                        <option value="3">3</option>--}}
    {{--                                                        <option value="4">4</option>--}}
    {{--                                                        <option value="5">5</option>--}}
    {{--                                                        <option value="6">6</option>--}}
    {{--                                                        <option value="7">7</option>--}}
    {{--                                                        <option value="8">8</option>--}}
    {{--                                                        <option value="9">9</option>--}}
    {{--                                                        <option value="10">10</option>--}}
    {{--                                                        <option value="11">11</option>--}}
    {{--                                                        <option value="12">12</option>--}}
    {{--                                                        <option value="13">13</option>--}}
    {{--                                                        <option value="14">14</option>--}}
    {{--                                                        <option value="15">15</option>--}}
    {{--                                                        <option value="16">16</option>--}}
    {{--                                                        <option value="17">17</option>--}}
    {{--                                                        <option value="18">18</option>--}}
    {{--                                                        <option value="19">19</option>--}}
    {{--                                                        <option value="20">20</option>--}}
    {{--                                                        <option value="21">21</option>--}}
    {{--                                                        <option value="22">22</option>--}}
    {{--                                                        <option value="23">23</option>--}}
    {{--                                                    </select></div>--}}
    {{--                                                <div class="col-auto px-0"><h6 class="bold mb-0">Giờ</h6>--}}
    {{--                                                </div>--}}
    {{--                                                <div class="col-auto"><h6 class="bold mb-0">đến</h6></div>--}}
    {{--                                                <div class="col"><select required=""--}}
    {{--                                                                         class="select-gray custom-select form-control"--}}
    {{--                                                                         id="tgctt_den" name="tgctt_den">--}}
    {{--                                                        <option value="0">0</option>--}}
    {{--                                                        <option value="1">1</option>--}}
    {{--                                                        <option value="2">2</option>--}}
    {{--                                                        <option value="3">3</option>--}}
    {{--                                                        <option value="4">4</option>--}}
    {{--                                                        <option value="5">5</option>--}}
    {{--                                                        <option value="6">6</option>--}}
    {{--                                                        <option value="7">7</option>--}}
    {{--                                                        <option value="8">8</option>--}}
    {{--                                                        <option value="9" selected>9</option>--}}
    {{--                                                        <option value="10">10</option>--}}
    {{--                                                        <option value="11">11</option>--}}
    {{--                                                        <option value="12">12</option>--}}
    {{--                                                        <option value="13">13</option>--}}
    {{--                                                        <option value="14">14</option>--}}
    {{--                                                        <option value="15">15</option>--}}
    {{--                                                        <option value="16">16</option>--}}
    {{--                                                        <option value="17">17</option>--}}
    {{--                                                        <option value="18">18</option>--}}
    {{--                                                        <option value="19">19</option>--}}
    {{--                                                        <option value="20">20</option>--}}
    {{--                                                        <option value="21">21</option>--}}
    {{--                                                        <option value="22">22</option>--}}
    {{--                                                        <option value="23">23</option>--}}
    {{--                                                    </select></div>--}}
    {{--                                                <div class="col-auto pl-0"><h6 class="bold mb-0">Giờ</h6>--}}
    {{--                                                </div>--}}
    {{--                                            </div>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"--}}
    {{--                                                                            for="">Số ngày mua chạy Auto--}}
    {{--                                            tương--}}
    {{--                                            tác:</label>--}}
    {{--                                        <div class="col-sm-8">--}}
    {{--                                            <div class="card card-orange mt-2"><select onchange="checkOutCoin()"--}}
    {{--                                                                                       class="form-control custom-select select-light"--}}
    {{--                                                                                       id="snmcatt"--}}
    {{--                                                                                       name="snmcatt">--}}
    {{--                                                    <option value="10">10</option>--}}
    {{--                                                    <option value="30">30</option>--}}
    {{--                                                    <option value="60">60</option>--}}
    {{--                                                    <option value="90">90</option>--}}
    {{--                                                </select></div>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"--}}
    {{--                                                                            for="">Black List Từ--}}
    {{--                                            Khóa:</label>--}}
    {{--                                        <div class="col-sm-8">--}}
    {{--                                            <div class="form-group">--}}
    {{--                                                <div class="form-group">--}}
    {{--                                                            <textarea rows="2" type="text"--}}
    {{--                                                                      class="form-control"--}}
    {{--                                                                      id="blacklisttukhoa"--}}
    {{--                                                                      name="blacklisttukhoa"--}}
    {{--                                                                      placeholder="Nhập list từ khóa có chứa trong bài viết mà bạn không muốn BOT chạy tương tác, ngăn cách nhau bởi dấu , (Vd : buồn, đám tang, chia buồn)"></textarea>--}}
    {{--                                                </div>--}}
    {{--                                            </div>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group row mt-4"><label class="col-sm-4 col-form-label"--}}
    {{--                                                                            for="">Black List ID:</label>--}}
    {{--                                        <div class="col-sm-8">--}}
    {{--                                            <div class="form-group">--}}
    {{--                                                <div class="form-group">--}}
    {{--                                                            <textarea rows="2" type="text"--}}
    {{--                                                                      class="form-control"--}}
    {{--                                                                      id="blacklistid"--}}
    {{--                                                                      name="blacklistid"--}}
    {{--                                                                      placeholder="Nhập list ID bạn không muốn BOT chạy tương tác, ngăn cách nhau bởi dấu , (Vd : 100047535830919,100047535830919)"></textarea>--}}
    {{--                                                </div>--}}
    {{--                                            </div>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group row"><label class="col-sm-4 col-form-label"--}}
    {{--                                                                       for="">Ghi--}}
    {{--                                            chú:</label>--}}
    {{--                                        <div class="col-sm-8"><textarea rows="2"--}}
    {{--                                                                        placeholder="Nhập nội dung ghi chú về tiến trình của bạn"--}}
    {{--                                                                        class="form-control input-gray"--}}
    {{--                                                                        id="gc"--}}
    {{--                                                                        name="gc"></textarea></div>--}}
    {{--                                    </div>--}}
    {{--                                    <div class="form-group mt-3">--}}
    {{--                                        <div--}}
    {{--                                            class="alert alert-success border-0 alert-dismissible fade show text-center rounded-10"--}}
    {{--                                            role="alert">--}}
    {{--                                            <button type="button" class="btn-close" data-bs-dismiss="alert"--}}
    {{--                                                    aria-label="Close"></button>--}}
    {{--                                            <h3><span id="check_out_coin">0</span> vnđ</h3>--}}
    {{--                                            <strong>Tổng tiền thanh toán</strong>--}}
    {{--                                            <p class="mb-0 mt-3 text-center">Bạn sẽ mua <span--}}
    {{--                                                    id="txt_quantity">0</span>ngày bot tương tác với giá <span--}}
    {{--                                                    id="txt_price_per">0</span>--}}
    {{--                                                vnđ / tương tác</p>--}}
    {{--                                        </div>--}}
    {{--                                    </div>--}}
    {{--                                    <button type="button" onclick="buy()"--}}
    {{--                                            class="btn mb-3 rounded-10 btn-dark fw-bold w-100">Mua--}}
    {{--                                        dịch vụ--}}
    {{--                                    </button>--}}
    {{--                                </div>--}}

    {{--                            </form>--}}
    {{--                        </div>--}}
    {{--                        <div class="modal-footer">--}}
    {{--                            <button type="submit" class="btn btn-primary">Save changes</button>--}}
    {{--                        </div>--}}
    {{--                    </form>--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}
    {{--    <script>--}}
    {{--        function showModalUpdateCookie(id) {--}}
    {{--            $('#modal_update_cookie').modal('toggle');--}}
    {{--            $('#id_cookie').val(id);--}}
    {{--        }--}}
    {{--    </script>--}}
@endsection
