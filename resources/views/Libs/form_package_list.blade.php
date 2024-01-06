<div class="form-group mt-3">
    <label class="fw-bold mb-2">Loại :</label>
    @foreach($package as $i=>$item)
        @if(true)
            <div class="form-check">
                <input type="radio" id="customRadio{{$i}}" name="package_name"
                       {{($i==0) ?'checked':''}}
                       value="{{$item->package_name}}" data-price="{{$item->prices}}" class="form-check-input">
                <label class="form-check-label" for="customRadio{{$i}}">{{$item->name}}
                    <span class="text-danger">{{$item->prices}} {{getCurrency()}}</span>
                    @if($item->status == 1)
                        @if($item->sl)
                            <span class="badge bg-custom-3">{{$item->sl}}</span></label>
                @endif
                <span class="badge bg-custom-9">Hoạt động</span>{!! $item->message !!}
                @endif
                @if(isset($item->config_package))
                    <span>{{$item->config_package}}</span>
                @endif
            </div>
        @endif
    @endforeach
</div>
