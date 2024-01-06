@if(isset($notify[0]))
    <div class="modal fade exampleModal " id="modal_notify_home" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-11 border-0">
                <div class="modal-header">
                    <h3 class="modal-title text-info" id="exampleModalLabel">{{$notify[0]->title}}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{--                <div class="modal-body text-center">--}}
                {{--                    <img class="img-modal" src="/assets/images/custom/welcome.gif">--}}
                {{--                    {!! $menu->guide !!}--}}
                {{--                    <button data-bs-dismiss="modal" class="btn bg-dark rounded-10 border-0 text-white px-5">OK</button>--}}
                {{--                </div>--}}
                <div class="modal-body text-center">
                    <img class="img-modal" src="/assets/images/custom/welcome.gif">
                    <p class="font-18"></p>
                    <h1 style="color: red;font-size: 24px">{!! $notify[0]->content !!}</h1>
                    <button data-bs-dismiss="modal" class="btn bg-dark rounded-10 border-0 text-white px-5">OK
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
<div class="modal fade exampleModal " id="modal_notify_home_warning" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-11 border-0">
            <div class="modal-header">
                <h3 class="modal-title text-info" id="exampleModalLabel">CẢNH BÁO LỪA ĐẢO</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{--                <div class="modal-body text-center">--}}
            {{--                    <img class="img-modal" src="/assets/images/custom/welcome.gif">--}}
            {{--                    {!! $menu->guide !!}--}}
            {{--                    <button data-bs-dismiss="modal" class="btn bg-dark rounded-10 border-0 text-white px-5">OK</button>--}}
            {{--                </div>--}}
            <div class="modal-body text-center">
                <img class="img-modal" src="/assets/images/gia-mao.jpg?v=1">
                <p class="font-18">
                    Cảnh báo lừa đảo. Các bạn chỉ giao dịch với các stk mang tên trần ngọc thu và qua zalo chính
                    0523169999
                    <a target="_blank" href="/assets/images/gia-mao.jpg?v=1">Link xem ảnh full hd</a>
                </p>
                <h1 style="color: red;font-size: 24px"></h1>
                <button data-bs-dismiss="modal" class="btn bg-dark rounded-10 border-0 text-white px-5">Tôi sẽ cẩn thận
                </button>
            </div>
        </div>
    </div>
</div>
<script>

</script>
