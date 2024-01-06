<form action="{{getUri(true)}}" method="get" id="form_search">
    @include('Libs.success_message')
    @if(isset($_GET['status']))
        <input name="status" type="text" class="form-control" style="display: none" value="{{$_GET['status']}}"
               placeholder="Tìm nhật ký theo username , object_id , user_id">
    @endif
    <div class="row form-group align-items-center">
        <div class="col-md-auto col-6 mb-3">
            <h6 class="fw-bold mb-0">Nhập id:</h6>
        </div>
        <div class="col-md col-12 mb-3">
            <input onkeypress="return runScript(event)" name="key" class="form-control rounded-10"
                   placeholder="Nhập từ khóa để tìm">
        </div>
        <div class="col-md-auto col-6 mb-3">
            <h6 class="fw-bold mb-0">Tải tối đa:</h6>
        </div>
        <div class="col-md col-12 mb-3">
            <select onchange="searchData()" name="limit" id="limit" class="form-select select-light rounded-10">
                <option {{checkLimit(10)}} value="100">Load 10 Nhật ký</option>
                <option {{checkLimit(15)}} value="100">Load 15 Nhật ký</option>
                <option {{checkLimit(200)}} value="200">Load 200 Nhật ký</option>
                <option {{checkLimit(300)}} value="300">Load 300 Nhật ký</option>
                <option {{checkLimit(400)}} value="400">Load 400 Nhật ký</option>
                <option {{checkLimit(500)}} value="500">Load 500 Nhật ký</option>
            </select>
        </div>
    </div>
</form>
<script>
    function searchData() {
        loading();
        $('#form_search').submit();
    }

    function runScript(e) {
        //See notes about 'which' and 'key'
        if (e.keyCode == 13) {
            searchData();
            return false;
        }
    }
</script>

<?php
function checkLimit($i)
{
    $limit = $_GET['limit'] ?? 100;
    if ($limit == $i) {
        return 'selected';
    }
    return '';
}
?>
