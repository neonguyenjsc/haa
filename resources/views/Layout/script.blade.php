{{--<script src="/assets/js/dark-mode-switcher.js"></script>--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script>
    $(function () {
        $(".knob").knob();
    });
</script>
<script>
    function getValueRadioByName(name) {
        return $('input[name="' + name + '"]:checked').val();
    }

    function getValueSelectByName(name) {
        return $('select[name=' + name + '] option').filter(':selected').val()
    }

    var chu_y_modal = 'Bạn đã đọc chú ý và lưu ý và bạn đã kiểm tra lại đơn hàng ?';
    if (localStorage.getItem("theme-color")) {
        theme = localStorage.getItem("theme-color");
        if (theme === '#232a3b') {
            $(".dark-mode-switchers__toggle").addClass("dark-mode-switcher__toggle--active");
            $(".dark-mode-switchers__toggle").removeClass("border");
            $(".dark-mode-switchers__toggle").removeClass("border-custom");
            $('html').removeClass('light')
            $('html').addClass('dark')
            $('.app').css('background', '#232a3b')
        } else {
            $(".dark-mode-switchers__toggle").removeClass("dark-mode-switcher__toggle--active");
            $(".dark-mode-switchers__toggle").addClass("border");
            $(".dark-mode-switchers__toggle").addClass("border-custom");
            $('html').removeClass('dark')
            $('html').addClass('light')
            $('.app').css('background', '#1C3FAA')
        }
    }

    /*modal*/

    function modalSuccess(message) {
        swal({
            title: "Thành công",
            text: message,
            icon: "success",
        });
    }

    function modalError(message) {
        swal({
            title: "Thất bại",
            text: message,
            icon: "error",
        });
    }

    function modalSubmitForm(message, form) {
        swal({
            title: "Bạn có chắc chắn ?",
            text: message,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    $('#' + form).submit();
                    loading();
                }
            });
    }

    function loading() {
        $('body').append('<div id="loader-wrapper">' +
            '<h5 class="text-center text-danger mt-2">Đơn đang trong quá trình tạo. vui lòng đợi quá trình này kết thúc. Sau khi hoàn thành. hãy kiểm tra tại nhật ký tạo đơn</h5>\n' +
            '    <div id="loader"></div>\n' +
            '</div>')
    }

    function stopLoading() {
        $('#loader-wrapper').remove();
    }

    function getDataAttRadio(name, data_att) {
        return $('input[name="' + name + '"]:checked').data(data_att);
    }

    function getDataInput(name) {
        return $('input[name="' + name + '"]').val();
    }

    function addValueInput(name, value) {
        $('input[name="' + name + '"]').val(value);
    }

    function addTextId(id, txt) {
        $('#' + id).text(txt);
    }

    function formatNumber(num) {
        return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
    }

    function copy(id) {
        var copy = document.getElementById(id);
        $("#" + id).prop("disabled", false);
        copy.select();
        document.execCommand("copy");
        $("#" + id).prop("disabled", true);
        modalSuccess("Copy thành công " + copy.value);
    }

    function removeOrder(id, url, price_remove = 1000) {
        swal({
            title: "Chú ý",
            text: "Bạn có chắc chắn hủy đơn " + id + " với phí dịch vụ là " + price_remove,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    loading();
                    window.location.href = url;
                }
            });
    }

    function removeOrderVip(id, url, price_remove = 1000) {
        swal({
            title: "Chú ý",
            text: "Bạn có chắc chắn hủy đơn " + id + " với phí dịch vụ là " + price_remove + " và 7 ngày vip ",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    loading();
                    window.location.href = url;
                }
            });
    }

    function warrantyOrder(id, url) {
        swal({
            title: "Chú ý",
            text: "Bạn có muốn bảo hành đơn " + id,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    loading();
                    window.location.href = url;
                }
            });
    }

    function checkStatusOrder(id, url) {
        swal({
            title: "Chú ý",
            text: "Bạn có muốn kiểm tra tiến trình của đơn hàng số #" + id,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    loading();
                    window.location.href = url;
                }
            });
    }

    $(document).ready(function () {
        $('#modal_guide').modal('toggle');
        // $('#modal_notify_home_warning').modal('toggle');
    });
</script>
