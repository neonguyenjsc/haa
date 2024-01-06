@extends('index')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <h6 class="mb-0 text-uppercase">Tích hợp site đại lý
            </h6>
            <hr/>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label fw-bold">Key kích hoạt</label>
                                <input id="api_key" type="text" value="{{\Illuminate\Support\Facades\Auth::user()->api_key}}"
                                       disabled class="form-control">
                                <button onclick="copy('api_key')"
                                        class="btn fw-bold rounded-10 text-white bg-dark w-50 mx-auto d-block mt-3">Copy
                                </button>
                            </div>
                            <div class="card mt-3 bg-custom-2 border-0 rounded-10">
                                <div class="card-body">
                                    <p class="fw-bold">Hướng dẫn:</p>
                                    <p><span class="fw-bold">- Bước 1 :</span> <span>Mua tên miền (đọc lưu ý trước khi
                                        mua)</span></p>
                                    <p><span class="fw-bold">- Bước 2 :</span> <span>Cài đặt ns1: <b
                                                class="text-white">celeste.ns.cloudflare.com</b></span></p>
                                    <p><span class="fw-bold">- Bước 3 :</span> <span>Cài đặt ns2: <b
                                                class="text-white">watson.ns.cloudflare.com</b></span></p>
                                    <p><span class="fw-bold">- Bước 4 :</span> Truy cập vào trang của bạn và nhập api
                                        key
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-custom-3 rounded-10">
                                <div class="card-body text-white">
                                    <p class="fw-bold">Chú ý:</p>
                                    <p>- Không dùng tên miền chứa các từ <span
                                            class="fw-bold">facebook,instagram,youtube,tiktok,...</span>
                                        có
                                        liên quan đến thương hiệu. Tránh bị kiện</p>
                                    <p>- Không share key kích hoạt này tránh mất tiền</p>
                                    <p>- Nếu bị lộ key hãy đổi mật khẩu</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
