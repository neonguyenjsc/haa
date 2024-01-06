<hr>
{{$data->onEachSide(2)->appends(request()->except('page'))->links('pagination::bootstrap-4')}}
