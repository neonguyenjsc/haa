@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="order" role="tabpanel" aria-labelledby="home-tab">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="p-15">
                                        <div class="box-header with-border pb-2">
                                            <h4 class="box-title">{{$menu->name}}<strong></strong>
                                            </h4>
                                        </div>
                                        <h6><a href="/admin/package"><span
                                                    class="badge bg-warning">Quay lại</span></a></h6>
                                        @include('Libs.success_message')

                                        <form action="/admin/package/prices/update"
                                              method="POST"
                                              id="order_form">
                                            @foreach($data as $item)
                                                <h5 class="font-weight-bold">{{$item->name}}
                                                    => <span class="text-danger">{{$item->level->name}}</span></h5>

                                                <div class="form-group">
                                                    <label class="font-weight-bold">Giá order</label>
                                                    <input type="number" name="prices[]"
                                                           class="form-control rounded-10" min="0" step="0.01"
                                                           value="{{$item->prices}}">
                                                    <input id="object_id" type="hidden" name="id[]"
                                                           class="form-control rounded-10"
                                                           value="{{$item->id}}" required
                                                           placeholder="">
                                                </div>
                                                <hr>
                                            @endforeach
                                            <div class="box-footer">
                                                <button id="btn_buy_order" type="submit"
                                                        class="btn rounded-10 btn-primary font-weight-bold w-100">
                                                    Cập
                                                    nhật
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
        </div>
    </div>
@endsection
