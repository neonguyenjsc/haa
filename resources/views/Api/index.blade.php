@extends('index')
@section('content')
    <?php
    $domain = 'https://dichvu.baostar.pro';
    ?>
    <div class="page-wrapper">
        <div class="page-content">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content py-3">
                        <h4>API TÍCH HỢP</h4>
                        <div class="tab-pane fade show active" id="successhome" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">

                                    <h6 id="tong_quan" class="text-danger">Tổng quan</h6>
                                    <p>api-key : <b>{{getInfoUser('api_key')}}</b></p>
                                    <a href="https://documenter.getpostman.com/view/8796302/Uz5CKxHQ">Tài liệu</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
