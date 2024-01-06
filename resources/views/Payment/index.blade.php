@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <h6 class="mb-0 text-uppercase">NẠP TIỀN
            </h6>
            <hr/>
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-success" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#successhome" role="tab"
                               aria-selected="true">
                                <div class="d-flex align-items-center">
                                    <div class="tab-icon"><i class='bx bx-home font-18 me-1'></i>
                                    </div>
                                    <div class="tab-title">Nạp bằng thẻ cào</div>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#successprofile" role="tab"
                               aria-selected="false">
                                <div class="d-flex align-items-center">
                                    <div class="tab-icon"><i class='bx bx-user-pin font-18 me-1'></i>
                                    </div>
                                    <div class="tab-title">Chuyển khoản</div>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content py-3">
                        <div class="tab-pane fade show active" id="successhome" role="tabpanel">
                            <div class="row mt-3">
                                <div class="col-md-4 ">
                                    @include('Libs.success_message')
                                    <form action="/nap-tien/card" method="POST" id="form_update">
                                        <div class="box-body">
                                            <div class="form-group">
                                                <label class="form-label fw-bold">Nhà mạng</label>
                                                <select name="Network" class="form-control rounded-10"
                                                        style="width: 100%">
                                                    @foreach($card as $item)
                                                        <option value="{{$item->alias}}">{{$item->name}} <span
                                                                style="color: red">(Chiết khấu {{$item->charge}} %)</span>
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group mt-3">
                                                <label class="form-label fw-bold">Mệnh giá</label>
                                                <small style="color: red">Chọn sai mệnh giá mất tiền</small>
                                                <select name="CardValue" class="form-control rounded-10"
                                                        style="width: 100%">
                                                    <option value="">-- Mệnh giá --</option>
                                                    <option value="10000">10,000 đ</option>
                                                    <option value="20000">20,000 đ</option>
                                                    <option value="30000">30,000 đ</option>
                                                    <option value="50000">50,000 đ</option>
                                                    <option value="100000">100,000 đ</option>
                                                    <option value="200000">200,000 đ</option>
                                                    <option value="300000">300,000 đ</option>
                                                    <option value="500000">500,000 đ</option>
                                                    <option value="1000000">1,000,000 đ</option>

                                                </select>
                                            </div>
                                            <div class="form-group mt-3">
                                                <label class="form-label fw-bold">Số seri</label>
                                                <input name="CardSeri" class="form-control rounded-10">
                                            </div>
                                            <div class="form-group mt-3">
                                                <label class="form-label fw-bold">Mã thẻ</label>
                                                <input name="CardCode" class="form-control rounded-10">
                                            </div>
                                        </div>
                                        <div class="box-footer mt-3">
                                            <button type="button" onclick="modalSubmitForm('Đã kiểm tra thông tin chính xác ?','form_update')"
                                                    class="btn mt-3 rounded-10 btn-dark fw-bold w-100"> Nạp thẻ

                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-8">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Seri</th>
                                                <th>Mạng</th>
                                                <th>Mệnh giá</th>
                                                <th>Trạng thái</th>
                                                <th>Ghi chú</th>
                                                <th>Thời gian</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($logs_card as $i=>$item)
                                                <tr>
                                                    <th scope="row">{{$i +1}}</th>
                                                    <td>{{$item->serial}}</td>
                                                    <td>{{$item->card}}</td>
                                                    <td>{{number_format($item->amount)}}</td>
                                                    <td class="{{$item->status_string['class']}}">{{$item->status_string['text']}}</td>
                                                    <td>{{$item->description}}</td>
                                                    <td>{{$item->created_at}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="successprofile" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="mb-3">Tỷ giá: 1 VND = 1 {{getCurrency()}}</h6>
                                    <h6 class="mb-3"><span class="text-danger"> Chú ý:</span> Vui lòng chuyển đúng cú
                                        pháp
                                        để hệ thống tự động cộng tiền. Nếu
                                        sai cú pháp mất thời gian + mất phí.</h6>
                                    <div>
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label fw-bold">Nội dung chuyển
                                                khoản:</label>
                                            <div class="col-sm">
                                                <div class="card border-0 rounded-10 bg-custom-1">
                                                    <div class="card-body p-20 text-center">
{{--                                                        <h4 class="fw-bold mb-0 text-white">--}}

{{--                                                        </h4>--}}
                                                        <input type="text" id="payment_syntax" style="text-align: center" class="form-control" disabled value="chucmung {{getInfoUser('username')}}">
                                                        <button type="button" onclick="copy('payment_syntax')"
                                                                class="btn  bg-custom-7 rounded-10 text-white  btn-sm mt-2 py-2 px-5 font-13">
                                                            <i
                                                                class="bx bx-copy"></i> Copy
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($data)
                                            @foreach($data as $i=>$item)
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label fw-bold">Tài khoản số {{$i+1}}
                                                        : </label>
                                                    <div class="col-sm">
                                                        <div class="card border-0 bg-dark rounded-10 ">
                                                            <div class="card-body p-20 text-white">
                                                                <h6 class="text-white"><span
                                                                        class="fw-bold">{{$item->name ?? ''}}
                                                            tự động</span></h6>
                                                                <h6 class="text-white"><span class="fw-bold">{{$item->full_name ?? ''}}</span></h6>
                                                                <h6 class="text-white"><span
                                                                        class="fw-bold">{{$item->stk ?? ''}}</span></h6>
                                                                <h6 class="text-white"><span
                                                                        class="fw-bold">{{$item->branch ?? ''}}
                                                        </span></h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="car bg-custom-3 mb-4 rounded-10">
                                        <div class="card-body p-20">
                                            <div class="d-flex justify-content-between">
                                                <div class="d-flex font-size-19 text-white">
                                                    <i class="fas fa-wallet mr-2"></i>
                                                    <h6 class="mb-0 font-size-19 text-white">Số tiền hiện có</h6>
                                                </div>
                                                <h6 class="font-size-19 mb-0 text-white">{{getInfoUser('coin')}}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="car bg-custom-3 mb-4 rounded-10">
                                        <div class="card-body p-20">
                                            <div class="d-flex justify-content-between">
                                                <div class="d-flex font-size-19 text-white">
                                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                                    <h6 class="mb-0 font-size-19 text-white">Số tiền đã nạp</h6>
                                                </div>
                                                <h6 class="font-size-19 mb-0 text-white">{{number_format(getInfoUser('total_recharge'))}} đ</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="car bg-custom-3 rounded-10">
                                        <div class="card-body p-20">
                                            <h6 class="fw-bold text-white">Hướng dẫn nạp tiền: </h6>
                                            <h6 class="text-white">Liên hệ admin</h6>
                                        </div>
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
