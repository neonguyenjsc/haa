<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    //
    public function index(Request $request)
    {
        $account = User::where('role', 'admin')->whereNotIn('id', [3, 332])->get();
        return view('Rating.index', ['account' => $account]);
    }

    public function rating(Request $request)
    {
        if (getInfoUser('user_id') == $request->id) {
            return redirectBackSuccess("Thành công!");
        }

        $date = [
            date('Y-m-d 00:00:00'),
            date('Y-m-d 23:59:59'),
        ];
        if (!Rating::where('user_id', getInfoUser('id'))->whereBetween('created_at', $date)->first()) {
            $user_admin = User::find($request->id);
            Rating::newRating([
                'username' => getInfoUser('username'),
                'name' => getInfoUser('name'),
                'content' => $request->get('content'),
                'public' => 0,
                'user_id_admin' => $request->id,
                'user_id' => getInfoUser('id'),
                'start' => $request->rating,
                'username_admin' => $user_admin->username,
            ]);
        }
        return redirectBackSuccess("Thành công!!");
    }
}
