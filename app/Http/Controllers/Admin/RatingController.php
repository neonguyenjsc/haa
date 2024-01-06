<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Rating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    //

    public function allowUser()
    {
        $user = \request()->user_admin;
        $username = $user->username;
        if (!in_array($username, [
            'giapthanhquoc1126',
            'baostar9999',
        ])) {
            abort(403);
            return false;
        }
        return true;
    }

    public function index(Request $request)
    {
        $this->allowUser();
        $menu = Menu::find(35);
        if ($request->key == 'last_month') {
            $monthStartDate = Carbon::parse('first day of previous month')->format('Y-m-d 00:00:00');
            $monthEndDate = Carbon::parse('last day of previous month')->format('Y-m-d 23:59:59');
            $date = [
                $monthStartDate,
                $monthEndDate,
            ];
        } else {
            $now = Carbon::now();
            $monthStartDate = $now->startOfMonth()->format('Y-m-d 00:00:00');
            $monthEndDate = $now->endOfMonth()->format('Y-m-d 23:59:59');
            $date = [
                $monthStartDate,
                $monthEndDate,
            ];
        }
        $list = Rating::whereBetween('created_at', $date)->get();
        $account = User::where('role', 'admin')->whereNotIn('id', [3, 332])->get();
        foreach ($account as $item) {
            $rating_user = $list->where('user_id_admin', $item->id);
            $so_lan_rating = 0;
            $total_rating = 0;
            for ($i = 0; $i <= 5; $i++) {
                $kz = "rating_" . $i;
                $r = $rating_user->where('start', $i);
                $item->$kz = $r;
                $so_lan_rating = $so_lan_rating + count($r);
                $total_rating = $total_rating + (count($r) * $i);
            }
            $item->so_lan_rating = $so_lan_rating;
            $item->total_rating = $total_rating;
            if ($so_lan_rating > 0) {
                $item->average = $total_rating / $so_lan_rating;
            } else {
                $item->average = 0;
            }
        }
        return view('Admin.Rating.index', ['data' => $list, 'account' => $account, 'menu' => $menu]);
    }
}
