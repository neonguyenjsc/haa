<?php
$notes = $menu->notes ?? '';
$notes = explode("\n", $notes);
?>
@if(count($notes) > 1)
    <div class="alert alert-warning fade show mt-3 rounded-10"
         role="alert">
        <h5 style="color: red">Chú ý</h5>
        @foreach($notes as $item_notes)
            <p class="font-weight-bold">- {{$item_notes}}</p>
        @endforeach
    </div>
@endif

