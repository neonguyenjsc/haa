@extends('index')
@section('content')
    <?php
    ?>
    <style>
        .circle-image img {
            border: 6px solid #fff;
            border-radius: 100%;
            padding: 0px;
            top: -28px;
            position: relative;
            width: 70px;
            height: 70px;
            border-radius: 100%;
            z-index: 1;
            background: #e7d184;
            cursor: pointer
        }

        .dot {
            height: 18px;
            width: 18px;
            background-color: blue;
            border-radius: 50%;
            display: inline-block;
            position: relative;
            border: 3px solid #fff;
            top: -48px;
            left: 186px;
            z-index: 1000
        }

        .name {
            margin-top: -21px;
            font-size: 18px
        }

        .fw-500 {
            font-weight: 500 !important
        }

        .start {
            color: green
        }

        .stop {
            color: red
        }

        .rate {
            border-bottom-right-radius: 12px;
            border-bottom-left-radius: 12px
        }

        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center
        }

        .rating > input {
            display: none
        }

        .rating > label {
            position: relative;
            width: 1em;
            font-size: 30px;
            font-weight: 300;
            color: #FFD600;
            cursor: pointer
        }

        .rating > label::before {
            content: "\2605";
            position: absolute;
            opacity: 0
        }

        .rating > label:hover:before,
        .rating > label:hover ~ label:before {
            opacity: 1 !important
        }

        .rating > input:checked ~ label:before {
            opacity: 1
        }

        .rating:hover > input:checked ~ label:before {
            opacity: 0.4
        }

        .buttons {
            top: 36px;
            position: relative
        }

        .rating-submit {
            border-radius: 15px;
            color: #fff;
            height: 49px
        }

        .rating-submit:hover {
            color: #fff
        }
    </style>
    <div class="page-wrapper">
        <div class="page-content">
            <h6 class="mb-0 text-uppercase">Đánh giá chất lượng phục vụ
            </h6>
            <hr/>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @include('Libs.success_message')
                        @foreach($account as $i=>$item)
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="text-right cross"><i class="fa fa-times mr-2"></i></div>
                                    <div class="card-body text-center">
                                        <img src="{{$item->avatar}}"
                                             height="100" width="100">
                                        <div class="comment-box text-center">
                                            <h4>{{$item->name}} </h4>
                                            <form action="/rating/add" method="POST">
                                                <div class="rating">
                                                    <input type="radio" name="rating" value="5"
                                                           id="5{{$i}}"><label
                                                        for="5{{$i}}">☆</label>
                                                    <input type="radio" name="rating" value="4"
                                                           id="4{{$i}}"><label for="4{{$i}}">☆</label> <input
                                                        type="radio" name="rating" value="3" id="3{{$i}}"><label
                                                        for="3{{$i}}">☆</label>
                                                    <input type="radio" name="rating" value="2" id="2{{$i}}"><label
                                                        for="2{{$i}}">☆</label>
                                                    <input type="radio" name="rating" value="1" id="1{{$i}}"><label
                                                        for="1{{$i}}">☆</label>
                                                    <input style="display: none" value="{{$item->id}}" name="id">
                                                </div>
                                                <div class="comment-area"><textarea class="form-control" name="content"
                                                                                    placeholder="Hãy để lại lời đánh giá về nhân viên {{$item->name}}"
                                                                                    rows="4"></textarea></div>
                                                <div class="text-center mt-4">
                                                    <button type="submit" class="btn btn-success send px-5">Gửi đánh
                                                        giá
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
