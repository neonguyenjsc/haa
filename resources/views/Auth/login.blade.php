@include('Layout.head')
<body class="bg-login">
<!--wrapper-->
<div class="wrapper">
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container-fluid">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                <div class="col mx-auto">
                    <div class="mb-4 text-center">
                        {{--                        <img src="assets/images/logo-img.png" width="180" alt="" />--}}
                        <h1>{{getTitle()}}</h1>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="border p-4 rounded">
                                <div class="text-center">
                                    <h3 class="">Đăng nhập</h3>
                                    <p>Bạn chưa có tài khoản? <a href="/dang-ky">Đăng ký tại đây</a>
                                    </p>
                                </div>
                                <div class="login-separater text-center mb-4"><span>ĐĂNG NHẬP</span>
                                    <hr/>
                                </div>
                                <div class="form-body">
                                    @include('Libs.success_message')
                                    <form class="row g-3" action="/login" method="post" id="action_form">
                                        <div class="col-12">
                                            <label for="inputEmailAddress" class="form-label">Tên đăng nhập</label>
                                            <input type="text" name="username" class="form-control"
                                                   id="inputEmailAddress" placeholder="Tên đăng nhập">
                                        </div>
                                        <div class="col-12">
                                            <label for="inputChoosePassword" class="form-label">Mật khẩu</label>
                                            <div class="input-group" id="show_hide_password">
                                                <input type="password" class="form-control border-end-0"
                                                       id="inputChoosePassword" name="password" value=""
                                                       onkeypress="runScript(event)"
                                                       placeholder="Enter Password"> <a href="javascript:;"
                                                                                        class="input-group-text bg-transparent"><i
                                                        class='bx bx-hide'></i></a>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                       id="flexSwitchCheckChecked" disabled checked>
                                                <label class="form-check-label" for="flexSwitchCheckChecked">Remember
                                                    Me</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-end"><a style="cursor: pointer"
                                                                          onclick="">Forgot
                                                Password ?</a>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button type="button"
                                                        onclick="modalSubmitForm('Bạn đã chắc chắn chưa ?','action_form')"
                                                        class="btn btn-primary"><i
                                                        class="bx bxs-lock-open"></i>Sign in
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
</div>
<!--end wrapper-->
<!-- Bootstrap JS -->
<script src="assets/js/bootstrap.bundle.min.js"></script>
<!--plugins-->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<!--Password show & hide js -->
<script>
    $(document).ready(function () {
        $("#show_hide_password a").on('click', function (event) {
            event.preventDefault();
            if ($('#show_hide_password input').attr("type") == "text") {
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass("bx-hide");
                $('#show_hide_password i').removeClass("bx-show");
            } else if ($('#show_hide_password input').attr("type") == "password") {
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass("bx-hide");
                $('#show_hide_password i').addClass("bx-show");
            }
        });
    });

    function runScript(e) {
        //See notes about 'which' and 'key'
        if (e.keyCode == 13) {
            modalSubmitForm('Bạn đã chắc chắn chưa ?', 'action_form')
            return false;
        }
    }
</script>
@include('Layout.script')
<!--app JS-->
<script src="assets/js/app.js"></script>
</body>
