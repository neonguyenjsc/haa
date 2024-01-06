<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{

    public function index(Request $request)
    {
        $id = 750163;
        $limit = $request->limit ?? 50;
        $query = $request->q;
        if (Auth::user()->role == 'admin') {
            $data_ = Logs::select(['id'])->where(function ($q) use ($request, $query) {
                if ($query && in_array($query, ['username', 'object_id', 'orders_id'])) {
                    $q->where($query, $request->key);
                }
                if ($request->action && $request->action != null) {
                    $q->where('action', $request->action);
                }
                $package_name = $request->package_name;
                if ($package_name) {
                    $q->where('package_name', $package_name);
                }
                $user_id = $request->user_id;

                if ($user_id) {
                    $q->where('user_id', $user_id);
                }
                if (isset($request->s) && isset($request->e)) {
                    $q->whereBetween('created_at', $request->only('s', 'e'));
                }
            })->take($limit)->orderBy('id', 'DESC')->pluck('id')->toArray();
        } else {
            $data_ = Logs::select(['id'])->where('user_id', Auth::user()->id)->where(function ($q) use ($request) {
                $key = $request->key;
                if ($key) {
                    $q->orWhere('client_username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
                }
            })->take($limit)->orderBy('id', 'DESC')->pluck('id')->toArray();
        }
        $data = Logs::select(['user_id',
            'username',
            'action',
            'client_id',
            'client_username',
            'action_coin',
            'description',
            'coin',
            'old_coin',
            'new_coin',
            'object_id',
            'created_at',
            'orders_id',])->whereIn('id', $data_)->orderBy('id', 'DESC')->get();
        return view('Logs.index', ['data' => $data]);
    }
}
