<?php

namespace App\Http\Controllers\Baostar;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Lib;
use App\Models\Ads\Facebook\Facebook;
use Illuminate\Http\Request;

class BaostarController extends Controller
{
    //
    use Lib;

    public function index(Request $request)
    {
        $limit = $request->query('limit');
        $list = Facebook::whereIn('package_name', ['facebook_follow_sv22', 'facebook_like_v15']);
        $list = $this->buildQueryModel($list)->take($limit)->get();
        $data = [];
        foreach ($list as $i => $item) {
            $data[$i] = [
                'id' => $item->id,
                'object_id' => $item->object_id,
                'quantity' => $item->quantity,
                'count_is_run' => $item->count_is_run,
                'status' => $item->status,
                'start_like' => $item->start_like,
                'notes' => $item->notes,
            ];
        }
        return response()->json(['status' => 200, 'success' => true, 'data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $i = Facebook::whereIn('package_name', ['facebook_follow_sv22', 'facebook_like_v15'])->where('id', $id)->first();
        if ($i) {
            $i->status = $request->status;
            $i->count_is_run = $request->count_is_run;
            $i->start_like = $request->start_like;
            $i->save();
        }

        return response()->json(['status' => 200, 'success' => true, 'data' => []]);
    }
}
