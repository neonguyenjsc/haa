@if($menu->guide)
    <div class="modal fade exampleModal " id="modal_guide" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-11 border-0">
                <!-- <div class="modal-header">
                    <h3 class="modal-title text-info" id="exampleModalLabel">Thông báo chung</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div> -->
                {{--                <div class="modal-body text-center">--}}
                {{--                    <img class="img-modal" src="/assets/images/custom/welcome.gif">--}}
                {{--                    {!! $menu->guide !!}--}}
                {{--                    <button data-bs-dismiss="modal" class="btn bg-dark rounded-10 border-0 text-white px-5">OK</button>--}}
                {{--                </div>--}}
                <div class="modal-body text-center">
                    <img class="img-modal" src="/assets/images/custom/welcome.gif">
                    <p class="font-18">{!! $menu->guide !!}</p>
                    <button data-bs-dismiss="modal" class="btn bg-dark rounded-10 border-0 text-white px-5">OK</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {

        });
    </script>
@endif
