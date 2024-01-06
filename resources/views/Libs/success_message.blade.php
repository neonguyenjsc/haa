
@if (session('error'))
    <div class="alert alert-danger">
        <?php
        $error = session('error');
        ?>
        @foreach ($error->all() as $itemError)
            <p>{{ $itemError }}</p>
        @endforeach
    </div>
@endif
@if(session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
    </div>
@endif
@if(session('error_'))
    <div class="alert alert-danger">
        {{session('error_')}}
    </div>
@endif
@if(session('error__'))
    <?php
    $error__ = session('error__');
    ?>
    @foreach ($error__ as $itemError__)
        @foreach($itemError__ as $item)
            <div class="alert alert-danger">
                {{ $item }}
            </div>
        @endforeach
    @endforeach
@endif
@if(session('error___'))
    <?php
    $error__ = session('error___');
    ?>
    @foreach ($error__ as $itemError__)
        <div class="alert alert-danger">
            {{ $itemError__ }}
        </div>
    @endforeach
@endif
