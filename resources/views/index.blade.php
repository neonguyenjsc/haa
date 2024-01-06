@include('Layout.head')
<!-- Messenger Plugin chat Code -->
<div id="fb-root"></div>

<!-- Your Plugin chat code -->
<div id="fb-customer-chat" class="fb-customerchat">
</div>

<script>
    var chatbox = document.getElementById('fb-customer-chat');
    chatbox.setAttribute("page_id", "620131081773744");
    chatbox.setAttribute("attribution", "biz_inbox");
</script>

<!-- Your SDK code -->
<script>
    window.fbAsyncInit = function() {
        FB.init({
            xfbml            : true,
            version          : 'v14.0'
        });
    };

    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/vi_VN/sdk/xfbml.customerchat.js';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>
<script>
    var type_menu = 'like';

</script>
<body>
<!--wrapper-->
<div class="wrapper">
    <!--sidebar wrapper -->
{{--    <div id="sidebar"></div>--}}
@include('Layout.sidebar')
<!--end sidebar wrapper -->
    <!--start header -->
{{--    <div id="header"></div>--}}
@include('Layout.header')
<!--end header -->
    <!--start page wrapper -->
@yield('content')
<!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button-->
    <a href="javaScript:;" class="back-to-top"><i class="bx bxs-up-arrow-alt"></i></a>
    <!--End Back To Top Button-->
    {{--    <div id="footer"></div>--}}
    @include('Layout.footer')
    @include('Layout.modal')
</div>
<!--end wrapper-->
<!--start switcher-->
<div class="switcher-wrapper">
    <div class="switcher-btn"><i class="bx bx-cog bx-spin"></i></div>
    <div class="switcher-body">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 text-uppercase">Theme Customizer</h5>
            <button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
        </div>
        <hr/>
        <h6 class="mb-0">Theme Styles</h6>
        <hr/>
        <div class="d-flex align-items-center justify-content-between">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="lightmode" checked/>
                <label class="form-check-label" for="lightmode">Light</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="darkmode"/>
                <label class="form-check-label" for="darkmode">Dark</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="semidark"/>
                <label class="form-check-label" for="semidark">Semi Dark</label>
            </div>
        </div>
        <hr/>
        <div class="form-check">
            <input class="form-check-input" type="radio" id="minimaltheme" name="flexRadioDefault"/>
            <label class="form-check-label" for="minimaltheme">Minimal Theme</label>
        </div>
        <hr/>
        <h6 class="mb-0">Header Colors</h6>
        <hr/>
        <div class="header-colors-indigators">
            <div class="row row-cols-auto g-3">
                <div class="col">
                    <div class="indigator headercolor1" id="headercolor1"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor2" id="headercolor2"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor3" id="headercolor3"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor4" id="headercolor4"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor5" id="headercolor5"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor6" id="headercolor6"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor7" id="headercolor7"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor8" id="headercolor8"></div>
                </div>
            </div>
        </div>

        <hr/>
        <h6 class="mb-0">Sidebar Backgrounds</h6>
        <hr/>
        <div class="header-colors-indigators">
            <div class="row row-cols-auto g-3">
                <div class="col">
                    <div class="indigator sidebarcolor1" id="sidebarcolor1"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor2" id="sidebarcolor2"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor3" id="sidebarcolor3"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor4" id="sidebarcolor4"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor5" id="sidebarcolor5"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor6" id="sidebarcolor6"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor7" id="sidebarcolor7"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor8" id="sidebarcolor8"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end switcher-->
<!-- Bootstrap JS -->
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<!--plugins-->
<script src="/assets/plugins/simplebar/js/simplebar.min.js"></script>
<script src="/assets/plugins/metismenu/js/metisMenu.min.js"></script>
<script src="/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<script src="/assets/plugins/chartjs/js/Chart.min.js"></script>
<script src="/assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
<script src="/assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js"></script>
<script src="/assets/plugins/jquery.easy-pie-chart/jquery.easypiechart.min.js"></script>
<script src="/assets/plugins/sparkline-charts/jquery.sparkline.min.js"></script>
<script src="/assets/plugins/jquery-knob/excanvas.js"></script>
<script src="/assets/plugins/jquery-knob/jquery.knob.js"></script>

<script src="/assets/js/index.js"></script>
<!--app JS-->
{{--<script src="/assets/js/app.js"></script>--}}
{{--<script src="./load-pages.js"></script>--}}
</body>
<script>
    new PerfectScrollbar('.product-list');
    new PerfectScrollbar('.customers-list');

    function convertUid(provider = 'facebook') {
        let uid = $('input[name="object_id"]').val();
        if (provider === 'facebook') {
            if (isNaN(parseInt(uid))) {
                convertUidFacebook(uid, type_menu);
            }
        }
    }

    function convertUidFacebook(link, type, name = 'object_id') {
        $('input[name="' + name + '"]').val('');
        $('input[name="' + name + '"]').attr('placeholder', "Đang chuyển đổi link thành uid");
        $.ajax({
            "url": "/api/convert-uid",
            "method": "POST",
            "timeout": 0,
            "headers": {
                "Content-Type": "application/json"
            },
            "data": JSON.stringify({
                "type": type,
                "link": link
            }),
            success: function (data) {
                if (data == 0) {
                    modalError('Không đúng định dạng vui lòng thử lại hoặc nhập id thủ công');
                    $('input[name="' + name + '"]').attr('placeholder', "Không đúng định dạng vui lòng thử lại hoặc nhập id thủ công");
                } else {
                    modalSuccess("Chuyển đổi thành công");
                    $('input[name="' + name + '"').val(data);
                }
            }
        });
    }
</script>
</html>
