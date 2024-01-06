@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    {{--                    @include('Libs.form_tab_history')--}}
                    <div class="tab-content py-3">
                        <h4>{{$menu->name}}</h4>
                        <div class="box h-100 mt-3">
                            <h6><a href="/admin/package"><span
                                        class="badge bg-warning">Quay lại</span></a></h6>
                            <h3 class="p-3 profile-title">Thông tin gói </h3>
                            <div class="border-t border-gray-200 dark:border-dark-5 p-3">
                                <form action="/admin/package/update" method="POST" id="form_update">
                                    <input type="hidden" name="id" value="{{$data->id}}">
                                    <div class="form-group row pt-2">
                                        <label class="col-sm-2 col-form-label font-bold">Tên Dịch vụ: </label>
                                        <div class="col-sm-9">
                                            <input type="text" placeholder="Nhập tên tài khoản"
                                                   value="{{$data->menu->name}}"
                                                   required="required" disabled
                                                   class="form-control form-control-md rounded-10 border-gray ">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <label class="col-sm-2 col-form-label font-bold">Gói nguồn: </label>
                                        <div class="col-sm-9">
                                            <input type="text" placeholder="Gói nguồn"
                                                   value="{{$data->package_name_master}}"
                                                   required="required" name="package_name_master"
                                                   class="form-control form-control-md rounded-10 border-gray ">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <label class="col-sm-2 col-form-label font-bold">Tên Gói: </label>
                                        <div class="col-sm-9">
                                            <input type="text" placeholder="Nhập tên tài khoản" name="name"
                                                   value="{{$data->name}}"
                                                   required="required"
                                                   class="form-control form-control-md rounded-10 border-gray ">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <label class="col-sm-2 col-form-label font-bold">Ghi chú: </label>
                                        <div class="col-sm-9">
                                            <input type="text" placeholder="Nhập tên tài khoản" name="description"
                                                   value="{{$data->description}}"
                                                   class="form-control form-control-md rounded-10 border-gray ">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <label class="col-sm-2 col-form-label font-bold">Min: </label>
                                        <div class="col-sm-9">
                                            <input type="text" placeholder="Nhập tên tài khoản" name="min"
                                                   value="{{$data->min}}"
                                                   required="required"
                                                   class="form-control form-control-md rounded-10 border-gray ">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <label class="col-sm-2 col-form-label font-bold">Max: </label>
                                        <div class="col-sm-9">
                                            <input type="text" placeholder="Nhập tên tài khoản" name="max"
                                                   value="{{$data->max}}"
                                                   required="required"
                                                   class="form-control form-control-md rounded-10 border-gray ">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <label class="col-sm-2 col-form-label font-bold">message: </label>
                                        <div class="col-sm-9">
                                            <input type="text" placeholder="Nhập tên tài khoản" name="message"
                                                   value="{{$data->message}}"
                                                   class="form-control form-control-md rounded-10 border-gray ">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <label class="col-sm-2 col-form-label font-bold">Lưu ý: </label>
                                        <div class="col-sm-9">
                                            <a href="https://nightly.ckeditor.com/22-08-28-06-03/full/samples/"
                                               target="_blank">Làm cho đẹp</a>
                                            <textarea style="height: 200px" name="notes"
                                                      class="form-control">{{$data->notes}}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <label class="col-sm-2 col-form-label font-bold">Thứ tự: </label>
                                        <div class="col-sm-9">
                                            <input type="text" placeholder="Nhập tên tài khoản" name="sort"
                                                   value="{{$data->sort}}"
                                                   class="form-control form-control-md rounded-10 border-gray ">
                                        </div>
                                    </div>
                                    <div class="form-group row pt-2">
                                        <label class="col-sm-2 col-form-label font-bold">Trạng thái
                                            : @if($data->status == 1) <label class="text-success">Hoạt
                                                động</label> @else <label
                                                class="text-danger">Tạm khóa</label> @endif </label>

                                        <div class="col-sm-9">
                                            <select name="status" class="form-control">
                                                <option value="0" {{($data->status == 0) ?'selected' :''}}>Đang khóa
                                                </option>
                                                <option value="1" {{($data->status == 1) ?'selected' :''}}>Hoạt động
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <button id="btn_buy_order" type="submit"
                                            class="btn rounded-10 btn-primary font-weight-bold pt-2">
                                        Cập
                                        nhật
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
