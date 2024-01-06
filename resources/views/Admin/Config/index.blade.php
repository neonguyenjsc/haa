@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    {{--                    @include('Libs.form_tab_history')--}}
                    <div class="tab-content py-3">
                        <h4>Cài đặt api</h4>
                        <div class="box h-100 mt-3">
                            <div class="border-t border-gray-200 dark:border-dark-5 p-3">
                                @include('Libs.success_message')
                                <form action="/admin/config/update" method="post"
                                      id="order_form">
                                    @foreach($data as $item)

                                        <div class="col-md-12">
                                            <input type="hidden" name="id[]" value="{{$item->id}}">
                                            <div class="row form-group text-center">
                                                <div class="col-md-4 ">
                                                    <div class="form-group ">
                                                        <h6 style="color: red; font-weight: bold">{{$item->name}}</h6>
                                                        <input name="value[]" class="form-control"
                                                               value="{{$item->value}}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    @endforeach
                                </form>
                                <hr>
                                <div class="box-footer">
                                    <button id="btn_buy_order" onclick="submit()"
                                            class="btn rounded-10 btn-primary font-weight-bold w-100">
                                        Cập nhật
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content py-3">
                        <h4>Thêm token <span>Token hiện tại {{$token ?? ''}}</span></h4>
                        <div class="box h-100 mt-3">
                            <div class="border-t border-gray-200 dark:border-dark-5 p-3">
                                @include('Libs.success_message')
                                <form action="/admin/config/update-token" method="post"
                                      id="order_form">
                                    <div class="form-group">
                                        <textarea class="form-control" style="height: 200px" name="token"></textarea>
                                    </div>
                                    <div class="box-footer">
                                        <button id="btn_buy_order" type="submit"
                                                class="btn rounded-10 btn-primary font-weight-bold w-100">
                                            Thêm token
                                        </button>
                                    </div>
                                </form>
                                <hr>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function submit() {
            $('#order_form').submit();
        }
    </script>
@endsection
