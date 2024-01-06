@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content py-3">
                        <h4>{{$menu->name}}</h4>
                        <div class="tab-pane fade show active">
                            <div class="box-header with-border pb-2">
                                @include('Libs.success_message')
                                <h4 class="box-title">Cập nhật username<strong>
                                        #{{$user->username}}</strong></h4>
                            </div>
                            <h6><a href="/admin/khach-hang"><span
                                        class="badge badge-warning">Quay lại</span></a></h6>
                            <form action="/admin/khach-hang/update" method="POST" id="order_form">
                                <div class="box-body">
                                    <input type="hidden" name="id" value="{{$user->id}}">
                                    <div class="form-group pt-4">
                                        <label class="font-weight-bold">Tên đăng nhập :</label>
                                        <input id="object_id" type="text"
                                               class="form-control rounded-10" disabled
                                               value="{{$user->username}}"
                                               required placeholder="Nhập Id">
                                    </div>
                                    <div class="form-group pt-4">
                                        <label class="font-weight-bold">Tiền hiện tại :</label>
                                        <input name="coin" id="coin" type="text"
                                               class="form-control rounded-10"
                                               value="{{($user->coin)}}"
                                               required placeholder="{{number_format($user->coin)}}">
                                    </div>
                                    {{--                                    <div class="form-group pt-4">--}}
                                    {{--                                        <label class="font-weight-bold">Mật khẩu :</label>--}}
                                    {{--                                        <input name="password" id="password" type="text"--}}
                                    {{--                                               class="form-control rounded-10"--}}
                                    {{--                                               placeholder="Nhập mật khẩu">--}}
                                    {{--                                    </div>--}}
                                    <div class="form-group pt-4">
                                        <label class="font-weight-bold">Trạng thái
                                            <span
                                                class="{!! getStatusClass($user->status) !!}">{!! getStatusString($user->status) !!}</span></label>
                                        <select name="status" class="form-control">
                                            <option {{($user->status) ?'selected' :''}} value="1">Hoạt động</option>
                                            <option {{($user->status == 0) ?'selected' :''}} value="0">Khóa</option>
                                        </select>
                                    </div>

                                    <div class="form-group pt-4">
                                        <label class="font-weight-bold">Level
                                            <span
                                                class="{!! getStatusClass($user->status) !!}">{{$user->level_user->name}}</span></label>
                                        <select name="level" class="form-control">
                                            @foreach($level as $item_level)
                                                <option
                                                    {{($item_level->id == $user->level) ? 'selected':''}} value="{{$item_level->id
                                                    }}">{{$item_level->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <a href="/admin/khach-hang/reset-pass/{{$user->id}}">Khôi phục mật khẩu</a>
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
    <script>
        function convertTxt() {
            $('#coin_txt').text(formatNumber(parseInt($('#coin').val())));
        }
    </script>
@endsection
