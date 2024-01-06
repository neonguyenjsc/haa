@extends('index')
@section('content')
    <?php
    ?>
    <div class="page-wrapper">
        <div class="page-content">
            <h6 class="mb-0 text-uppercase">Tài khoản
            </h6>
            <hr/>
            <div class="card">
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-8">
                            @include('Libs.success_message')
                            <form action="/tai-khoan/update" method="post" id="form_update">
                                <div class="form-group row mb-3">
                                    <div class="col-md-3">
                                        <label class="fw-bold form-label">Tên tài khoản :</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="name" value="{{getInfoUser('name')}}"
                                               class="form-control rounded-10">
                                    </div>
                                </div>
                                <div class="form-group row mb-3">
                                    <div class="col-md-3">
                                        <label class="fw-bold form-label">Phone :</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="phone_number" value="{{getInfoUser('phone_number')}}"
                                               class="form-control rounded-10">
                                    </div>
                                </div>
                                <div class="form-group row mb-3">
                                    <div class="col-md-3">
                                        <label class="fw-bold form-label">Email :</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="email" value="{{getInfoUser('email')}}"
                                               class="form-control rounded-10">
                                    </div>
                                </div>
                                <div class="form-group row mb-3">
                                    <div class="col-md-3">
                                        <label class="fw-bold form-label">Ảnh đại diện :</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="avatar" value="{{getInfoUser('avatar')}}"
                                               class="form-control rounded-10">
                                    </div>
                                </div>
                                <div class="form-group row mb-3">
                                    <div class="col-md-3">
                                        <label class="fw-bold form-label">Mật khẩu cũ :</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="password" name="old_password" class="form-control rounded-10">
                                    </div>
                                </div>
                                <div class="form-group row mb-3">
                                    <div class="col-md-3">
                                        <label class="fw-bold form-label">Mật khẩu mới :</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="password" name="new_password" class="form-control rounded-10">
                                    </div>
                                </div>
                                <div class="form-group row mb-3">
                                    <div class="col-md-3">
                                        <label class="fw-bold form-label">Nhập lại mật khẩu mới :</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="password" name="confirm_password" class="form-control rounded-10">
                                    </div>
                                </div>
                                @if(!Auth::user()->telegram_id)
                                    <p style="color:red;">
                                        Tài khoản chưa xác thực <a href="https://t.me/baostar_pro_bot"
                                                                   target="_blank">Xác thực bằng telegram
                                            tại
                                            đây</a>
                                    </p>
                                @else
                                    <p class="text-success">Đã xác thực tài khoản</p>
                                @endif
                                <div class="form-group row mb-3">

                                    <div class="ml-auto col-md-9 offset-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="basic_checkbox_1"
                                                   checked="">
                                            <label for="basic_checkbox_1" class="form-check-label fw-bold"> Tôi đồng
                                                ý</label> &nbsp;&nbsp;&nbsp;&nbsp;
                                            <a href="#" class="change-info ">Thay đổi thông tin</a>
                                        </div>
                                        <button type="button" onclick="modalSubmitForm('Đồng ý thông tin ?','form_update')"
                                                class="btn mt-3 rounded-10 btn-dark fw-bold w-100"> Lưu
                                            thông
                                            tin

                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <div class=" p-3 bg-custom-3 rounded-10">

                                <div class="d-flex flex-column align-items-center text-center">
                                    <img src="{{getInfoUser('avatar') ?? 'assets/images/avatars/avatar-2.png'}}" alt="Admin"
                                         class="rounded-circle p-1 bg-primary" width="110">
                                    <div class="mt-3">
                                        <h4 class="text-white">{{getInfoUser('name')}}</h4>
                                        <p class="text-white mb-1">{{getInfoUser('level_user')->name}}</p>
                                        <p class="text-white font-size-sm">{{getInfoUser('coin')}}</p>
                                        <!-- <button class="btn btn-primary">Follow</button>
                                        <button class="btn btn-outline-primary">Message</button> -->
                                    </div>
                                </div>
                                <hr class="my-4">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0"> Email</h6>
                                        <span class="text-secondary">{{getInfoUser('email')}}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0">Phone</h6>
                                        <span class="text-secondary">{{getInfoUser('phone_number')}}</span>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
