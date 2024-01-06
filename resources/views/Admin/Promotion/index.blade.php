@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    {{--                    @include('Libs.form_tab_history')--}}
                    <div class="tab-content py-3">
                        <h4>Cài đặt khuyến mãi</h4>
                        <div class="box h-100 mt-3">
                            <div class="border-t border-gray-200 dark:border-dark-5 p-3">
                                <form action="/admin/promotion/update" method="post"
                                      id="order_form">
                                    <div class="box-body">
                                        <input type="hidden" value="{{$data->id}}" name="id">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Ngày bắt đầu</label>
                                            <input class="form-control" value="" name="start"
                                                   type="date">
                                            <small style="color: red">Hiện
                                                tại: {{date('d-m-Y H:i:s',strtotime($data->start))}}</small>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Ngày kết thúc</label>
                                            <input class="form-control" value="{{$data->end}}" name="end" type="date">
                                            <small style="color: red">Hiện
                                                tại: {{date('d-m-Y H:i:s',strtotime($data->end))}}</small>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Phần trăm khuyến mãi</label>
                                            <input class="form-control" type="number" name="value"
                                                   value="{{$data->value}}">
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <button id="btn_buy_order"
                                                class="btn rounded-10 btn-primary font-weight-bold w-100">
                                            Duyệt
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
