@extends('index')
@section('content')
    <script src="https://cdn.ckeditor.com/4.16.1/standard/ckeditor.js"></script>
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content py-3">
                        <h4>Tạo thông báo</h4>
                        <div class="tab-pane fade show active">
                            <div class="box-header with-border pb-2">
                                @include('Libs.success_message')
                            </div>
                            <h6><a href="/admin/notify"><span
                                        class="badge bg-warning">Quay lại</span></a></h6>
                            <form action="/admin/notify/add" method="POST" id="order_form">
                                <div class="box-body">
                                    <input type="hidden" name="id" value="1">
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Tiêu đề</label>
                                        <input type="text" class="form-control" name="title" id="exampleInputPassword1"
                                               placeholder="Tiêu đề">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Nội dung</label>
                                        <textarea id="editor1" name="content"></textarea>
                                    </div>
                                </div>
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
{{--    <script src='https://cdn.tiny.cloud/1/vdqx2klew412up5bcbpwivg1th6nrh3murc6maz8bukgos4v/tinymce/5/tinymce.min.js' referrerpolicy="origin">--}}
{{--    </script>--}}
    <script>
        CKEDITOR.replace( 'editor1' );
    </script>
@endsection
