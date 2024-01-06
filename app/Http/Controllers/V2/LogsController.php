<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Ads\Facebook\BotComment;
use App\Models\Ads\Facebook\Eyes;
use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\FacebookVip\VipComment;
use App\Models\Ads\FacebookVip\VipEyes;
use App\Models\Ads\FacebookVipSale\LikeClone;
use App\Models\Ads\Instagram\Instagram;
use App\Models\Ads\Instagram\InstagramVipLike;
use App\Models\Ads\Proxy\Proxy;
use App\Models\Ads\Shopee\Shopee;
use App\Models\Ads\TikTok\TikTok;
use App\Models\Ads\Twitter\Twitter;
use App\Models\Ads\Youtube\Youtube;
use App\Models\Logs;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = $request->q;
        $limit = $request->get('limit') ?? 100;
        if ($limit == 'all') {
            $limit = 1000;
        }
        if ($request->user->role == 'admin') {
            $log = Logs::where('id', '>', 5000000)->where(function ($q) use ($request, $query) {
                if ($query && in_array($query, ['username', 'object_id', 'orders_id'])) {
                    $q->where($query, $request->key);
                }
                if ($request->action && $request->action != null) {
                    $q->where('action', $request->action);
                }
            })->orderBy('id', 'desc')->take($limit)->get();
//            if (!in_array($query, ['object_id', 'orders_id']) && !$request->action) {
//                $log->take(intval($limit));
//            }
            $data = [];
        } else {
            //?action=&key=304809&limit=10&q=orders_id
            $log = Logs::where('user_id', $request->user->id)->where(function ($q) use ($request, $query) {
                if (in_array($query, ['object_id', 'orders_id'])) {
                    $q->where($query, $request->key);
                }
                if ($request->action && $request->action != null) {
                    $q->where('action', $request->action);
                }
            })->orderBy('id', 'desc')->get();
            if (!in_array($query, ['object_id', 'orders_id']) && !$request->action) {
                $log->take(intval($limit));
            }
            $data = [];
        }

        foreach ($log as $i => $item) {
            $action_coin = "-";
            if ($item->action_coin == 'in') {
                $action_coin = "+";
            }
            $data[$i] = [
                'id' => $item->id,
                'type' => $item->type,
                'action' => $item->action,
                'type_str' => $item->type_str,
                'new_coin' => $item->new_coin,
                'coin' => $item->coin,
                'old_coin' => $item->old_coin,
                'action_coin' => $action_coin,
                'object_id' => $item->object_id,
                'description' => $item->description,
                'orders_id' => $item->orders_id ?? null,
                'created_at' => $item->created_at ?? null,
                'time' => strtotime($item->created_at),
            ];
        }
        $this->res['data'] = $data;
        return returnResponseSuccess($this->res);
    }

    public function logOrderFollowCheap(Request $request)
    {
        $id = explode(",", $request->id);
        $logs_fb = Facebook::where('user_id', 10990)->whereIn('id', $id)->orderBy('id', 'desc')->take(10000)->get();
//        $logs_tiktok = TikTok::where('user_id', 10990)->whereIn('id', $id)->orderBy('id', 'desc')->take(10000)->get();
        $data = [];
        foreach ($logs_fb as $fb) {
            $data[] = $fb;
//            if ($fb->status_string == 'Chạy xong') {
//                $fb->count_is_run = $fb->quantity;
//            }
        }
//        foreach ($logs_tiktok as $fb) {
//            $data[] = $fb;
//        }
        $this->res['data'] = $data;
        return $this->setResponse($this->res);
    }

    public function logOrder(Request $request)
    {
        $limit = $request->limit ?? 50;
        $menu_id = $request->m_id ?? 0;
        if ($request->user->role == 'admin') {
            switch ($request->type) {
                case 'facebook':
                    $data = Facebook::where('menu_id', $menu_id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
//                $this->res['data'] = $z;
//                return $this->setResponse($this->res);
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'run_order' => $item->run_order,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                            'allow_warranty' => $item->allow_warranty,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_eyes':
                    $data = Eyes::where('menu_id', $menu_id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'run_order' => $item->run_order,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                            'allow_warranty' => $item->allow_warranty,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'instagram':
                    $data = Instagram::where('menu_id', $menu_id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'run_order' => $item->run_order,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                            'allow_warranty' => $item->allow_warranty,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'youtube':
                    $data = Youtube::where('menu_id', $menu_id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'run_order' => $item->run_order,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                            'allow_warranty' => $item->allow_warranty,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'tiktok':
                    $data = TikTok::where('menu_id', $menu_id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'run_order' => $item->run_order,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                            'allow_warranty' => $item->allow_warranty,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'shopee':
                    $data = Shopee::where('menu_id', $menu_id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'run_order' => $item->run_order,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                            'allow_warranty' => $item->allow_warranty,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_vip_like':
                    $data = LikeClone::where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('fb_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'time_expired' => strtotime($item->time_expired),
                            'total_post' => $item->total_post ?? 0,
                            'days' => $item->days ?? 0,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'renew' => $item->renew,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_vip_comment':
                    $data = VipComment::where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('fb_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'time_expired' => strtotime($item->time_expired),
                            'total_post' => $item->total_post ?? 0,
                            'days' => $item->days ?? 0,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'is_change_comment' => $item->is_change_comment,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_vip_eyes':
                    $data = VipEyes::where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('fb_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'time_expired' => strtotime($item->time_expired),
                            'days' => $item->num_dates ?? 0,
                            'total_post' => $item->number_of_lives ?? 0, //số
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'proxy':
                    $data = Proxy::where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();

                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'quantity' => 1,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'time' => strtotime($item->created_at),
                            'time_expired' => $this->addDaysWithDate($item->created_at, $item->quantity),
                            'days' => $item->quantity ?? 0,
                            'ip' => $item->ip ?? 0,
                            'port' => $item->port ?? 0,
                            'username' => $item->proxy_username ?? 0,
                            'password' => $item->proxy_password ?? 0,
                            'id_' => $item->orders_id ?? 0,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return returnResponseSuccess($this->res);
                    break;
                case 'bot_comment':
                    $data = BotComment::where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'object_id' => $item->fb_id,
                            'fb_id' => $item->fb_id,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'full_link' => 'https://facebook.com/' . $item->fb_id,
                            'prices' => $item->prices,
                            'time' => strtotime($item->created_at),
                            'time_expired' => $item->time_end,
                            'days' => $item->days ?? 0,
                            'username' => $item->proxy_username ?? 0,
                            'id_' => $item->orders_id ?? 0,
                            'created_at' => strtotime($item->created_at),
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return returnResponseSuccess($this->res);
                    break;
                case 'twitter':
                    $data = Twitter::where('menu_id', $menu_id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
//                $this->res['data'] = $z;
//                return $this->setResponse($this->res);
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'run_order' => $item->run_order,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                            'allow_warranty' => $item->allow_warranty,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'instagram_vip_like':
                    $data = InstagramVipLike::where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'time_expired' => strtotime($item->time_expired),
                            'total_post' => $item->total_post ?? 0,
                            'days' => $item->days ?? 0,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                default:
                    return $this->setResponse($this->res);
                    break;
            }
        } else {
            switch ($request->type) {
                case 'facebook':
                    $data = Facebook::where('menu_id', $menu_id)->where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
//                $this->res['data'] = $z;
//                return $this->setResponse($this->res);
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'run_order' => $item->run_order,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                            'allow_warranty' => $item->allow_warranty,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_eyes':
                    $data = Eyes::where('menu_id', $menu_id)->where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'instagram':
                    $data = Instagram::where('menu_id', $menu_id)->where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'youtube':
                    $data = Youtube::where('menu_id', $menu_id)->where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'tiktok':
                    $data = TikTok::where('menu_id', $menu_id)->where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'time_expired' => strtotime($item->time_expired),
                            'total_post' => $item->total_post ?? 0,
                            'days' => $item->days ?? 0,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'shopee':
                    $data = Shopee::where('menu_id', $menu_id)->where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_vip_like':
                    $data = LikeClone::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('fb_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'time_expired' => strtotime($item->time_expired),
                            'total_post' => $item->total_post ?? 0,
                            'days' => $item->days ?? 0,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'renew' => $item->renew,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_vip_comment':
                    $data = VipComment::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('fb_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'time_expired' => strtotime($item->time_expired),
                            'total_post' => $item->total_post ?? 0,
                            'days' => $item->days ?? 0,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'is_change_comment' => $item->is_change_comment,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_vip_eyes':
                    $data = VipEyes::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('fb_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'time_expired' => strtotime($item->time_expired),
                            'days' => $item->num_dates ?? 0,
                            'total_post' => $item->number_of_lives ?? 0, //số
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'proxy':
                    $data = Proxy::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();

                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'quantity' => 1,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'time' => strtotime($item->created_at),
                            'time_expired' => $this->addDaysWithDate($item->created_at, $item->quantity),
                            'days' => $item->quantity ?? 0,
                            'ip' => $item->ip ?? 0,
                            'port' => $item->port ?? 0,
                            'username' => $item->proxy_username ?? 0,
                            'password' => $item->proxy_password ?? 0,
                            'id_' => $item->orders_id ?? 0,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return returnResponseSuccess($this->res);
                    break;
                case 'bot_comment':
                    $data = BotComment::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'object_id' => $item->fb_id,
                            'fb_id' => $item->fb_id,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'full_link' => 'https://facebook.com/' . $item->fb_id,
                            'prices' => $item->prices,
                            'time' => strtotime($item->created_at),
                            'time_expired' => $item->time_end,
                            'days' => $item->days ?? 0,
                            'username' => $item->proxy_username ?? 0,
                            'id_' => $item->orders_id ?? 0,
                            'created_at' => strtotime($item->created_at),
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return returnResponseSuccess($this->res);
                    break;
                case 'twitter':
                    $data = Twitter::where('menu_id', $menu_id)->where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
//                $this->res['data'] = $z;
//                return $this->setResponse($this->res);
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'run_order' => $item->run_order,
                            'price_per_remove' => $item->price_per_remove,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                            'allow_warranty' => $item->allow_warranty,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                case 'instagram_vip_like':
                    $data = InstagramVipLike::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                        if (isset($request->package_name)) {
                            $q->where('package_name', $request->package_name);
                        }
                        if (isset($request->key)) {
                            $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                            $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                        }
                    })->orderBy('id', 'DESC')->take($limit)->get();
                    $z = [];
                    $z = $data;
                    foreach ($data as $i => $item) {
                        $x = "processing";
                        if ($item->status == 1) {
                            if ($item->quantity <= $item->count_is_run) {
                                $x = "done";
                            } else {

                                $x = "processing";
                            }
                        }
                        if ($item->status == 0) {
                            $x = "remove";
                        }
                        if ($item->status == -1) {
                            $x = "pause";
                        }
                        $z[$i] = (object)[
                            'id' => $item->id,
                            'start_like' => $item->start_like,
                            'status' => $x,
                            'count_is_run' => $item->count_is_run,
                            'object_id' => $item->object_id,
                            'quantity' => $item->quantity,
                            'server' => $item->server,
                            'price_per' => $item->price_per,
                            'prices' => $item->prices,
                            'full_link' => $item->full_link,
                            'time' => strtotime($item->created_at),
                            'show_action' => $item->show_action,
                            'is_check_order' => $item->is_check_order,
                            'allow_remove' => $item->allow_remove,
                            'price_per_remove' => $item->price_per_remove,
                            'time_expired' => strtotime($item->time_expired),
                            'total_post' => $item->total_post ?? 0,
                            'days' => $item->days ?? 0,
                            'status_string' => $item->status_string,
                            'status_class' => $item->status_class,
                            'notes' => $item->notes,
                        ];
                    }
                    $this->res['data'] = $z;
                    return $this->setResponse($this->res);
                    break;
                default:
                    return $this->setResponse($this->res);
                    break;
            }
        }

        $z = [];
        $z = $data;
        foreach ($data as $i => $item) {
            $x = "processing";
            if ($item->status == 1) {
                if ($item->quantity <= $item->count_is_run) {
                    $x = "done";
                } else {

                    $x = "processing";
                }
            }
            if ($item->status == 0) {
                $x = "remove";
            }
            if ($item->status == -1) {
                $x = "pause";
            }
            $z[$i] = (object)[
                'id' => $item->id,
                'start_like' => $item->start_like,
                'status' => $x,
                'count_is_run' => $item->count_is_run,
                'object_id' => $item->object_id,
                'quantity' => $item->quantity,
                'server' => $item->server,
                'price_per' => $item->price_per,
                'prices' => $item->prices,
                'full_link' => $item->full_link,
                'time' => strtotime($item->created_at),
                'show_action' => $item->show_action,
                'is_check_order' => $item->is_check_order,
                'allow_remove' => $item->allow_remove,
                'price_per_remove' => $item->price_per_remove,
                'time_expired' => strtotime($item->time_expired),
                'total_post' => $item->total_post ?? 0,
                'days' => $item->days ?? 0,
            ];
        }
        $this->res['data'] = $z;
        return $this->setResponse($this->res);
    }

    public function logOrderLv2(Request $request)
    {
        $limit = $request->limit ?? 50;
        $menu_id = $request->m_id ?? 0;
        $client_id = $request->client_id ?? 0;
        switch ($request->type) {
            case 'facebook':
                $data = Facebook::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
//                $this->res['data'] = $z;
//                return $this->setResponse($this->res);
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per_agency,
                        'prices' => $item->prices_agency,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'run_order' => $item->run_order,
                        'price_per_remove' => $item->price_per_remove,
                        'status_string' => $item->status_string,
                        'status_class' => $item->status_class,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            case 'facebook_eyes':
                $data = Eyes::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per_agency,
                        'prices' => $item->prices_agency,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'price_per_remove' => $item->price_per_remove,
                        'status_string' => $item->status_string,
                        'status_class' => $item->status_class,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            case 'instagram':
                $data = Instagram::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per_agency,
                        'prices' => $item->prices_agency,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'price_per_remove' => $item->price_per_remove,
                        'status_string' => $item->status_string,
                        'status_class' => $item->status_class,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            case 'youtube':
                $data = Youtube::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per_agency,
                        'prices' => $item->prices_agency,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'price_per_remove' => $item->price_per_remove,
                        'status_string' => $item->status_string,
                        'status_class' => $item->status_class,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            case 'tiktok':
                $data = TikTok::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per_agency,
                        'prices' => $item->prices_agency,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'price_per_remove' => $item->price_per_remove,
                        'time_expired' => strtotime($item->time_expired),
                        'total_post' => $item->total_post ?? 0,
                        'days' => $item->days ?? 0,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            case 'shopee':
                $data = Shopee::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per,
                        'prices' => $item->prices,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'price_per_remove' => $item->price_per_remove,
                        'status_string' => $item->status_string,
                        'status_class' => $item->status_class,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            case 'twitter':
                $data = Twitter::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per,
                        'prices' => $item->prices,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'price_per_remove' => $item->price_per_remove,
                        'status_string' => $item->status_string,
                        'status_class' => $item->status_class,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            case 'facebook_vip_like':
                $data = LikeClone::where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('fb_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per,
                        'prices' => $item->prices,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'price_per_remove' => $item->price_per_remove,
                        'time_expired' => strtotime($item->time_expired),
                        'total_post' => $item->total_post ?? 0,
                        'days' => $item->days ?? 0,
                        'status_string' => $item->status_string,
                        'status_class' => $item->status_class,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            case 'facebook_vip_comment':
                $data = VipComment::where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('fb_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per,
                        'prices' => $item->prices,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'price_per_remove' => $item->price_per_remove,
                        'time_expired' => strtotime($item->time_expired),
                        'total_post' => $item->total_post ?? 0,
                        'days' => $item->days ?? 0,
                        'status_string' => $item->status_string,
                        'status_class' => $item->status_class,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            case 'facebook_vip_eyes':
                $data = VipEyes::where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('fb_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per,
                        'prices' => $item->prices,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'price_per_remove' => $item->price_per_remove,
                        'time_expired' => strtotime($item->time_expired),
                        'days' => $item->num_dates ?? 0,
                        'total_post' => $item->number_of_lives ?? 0, //số
                        'status_string' => $item->status_string,
                        'status_class' => $item->status_class,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            case 'proxy':
                $data = Proxy::where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();

                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'quantity' => 1,
                        'server' => $item->server,
                        'price_per' => $item->price_per,
                        'prices' => $item->prices,
                        'time' => strtotime($item->created_at),
                        'time_expired' => $this->addDaysWithDate($item->created_at, $item->quantity),
                        'days' => $item->quantity ?? 0,
                        'ip' => $item->ip ?? 0,
                        'port' => $item->port ?? 0,
                        'username' => $item->proxy_username ?? 0,
                        'password' => $item->proxy_password ?? 0,
                        'id_' => $item->orders_id ?? 0,
                    ];
                }
                $this->res['data'] = $z;
                return returnResponseSuccess($this->res);
                break;
            case 'bot_comment':
                $data = BotComment::where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'object_id' => $item->fb_id,
                        'fb_id' => $item->fb_id,
                        'server' => $item->server,
                        'price_per' => $item->price_per,
                        'full_link' => 'https://facebook.com/' . $item->fb_id,
                        'prices' => $item->prices,
                        'time' => strtotime($item->created_at),
                        'time_expired' => $item->time_end,
                        'days' => $item->days ?? 0,
                        'username' => $item->proxy_username ?? 0,
                        'id_' => $item->orders_id ?? 0,
                        'created_at' => strtotime($item->created_at),
                    ];
                }
                $this->res['data'] = $z;
                return returnResponseSuccess($this->res);
                break;

            case 'instagram_vip_like':
                $data = InstagramVipLike::where('client_id', $client_id)->where(function ($q) use ($request) {
                    if (isset($request->package_name)) {
                        $q->where('package_name', $request->package_name);
                    }
                    if (isset($request->key)) {
                        $q->orWhere('id', 'LIKE', '%' . $request->key . '%');
                        $q->orWhere('object_id', 'LIKE', '%' . $request->key . '%');
                    }
                })->orderBy('id', 'DESC')->take($limit)->get();
                $z = [];
                $z = $data;
                foreach ($data as $i => $item) {
                    $x = "processing";
                    if ($item->status == 1) {
                        if ($item->quantity <= $item->count_is_run) {
                            $x = "done";
                        } else {

                            $x = "processing";
                        }
                    }
                    if ($item->status == 0) {
                        $x = "remove";
                    }
                    if ($item->status == -1) {
                        $x = "pause";
                    }
                    $z[$i] = (object)[
                        'id' => $item->id,
                        'start_like' => $item->start_like,
                        'status' => $x,
                        'count_is_run' => $item->count_is_run,
                        'object_id' => $item->object_id,
                        'quantity' => $item->quantity,
                        'server' => $item->server,
                        'price_per' => $item->price_per,
                        'prices' => $item->prices,
                        'full_link' => $item->full_link,
                        'time' => strtotime($item->created_at),
                        'show_action' => $item->show_action,
                        'is_check_order' => $item->is_check_order,
                        'allow_remove' => $item->allow_remove,
                        'price_per_remove' => $item->price_per_remove,
                        'time_expired' => strtotime($item->time_expired),
                        'total_post' => $item->total_post ?? 0,
                        'days' => $item->days ?? 0,
                        'status_string' => $item->status_string,
                        'status_class' => $item->status_class,
                    ];
                }
                $this->res['data'] = $z;
                return $this->setResponse($this->res);
                break;
            default:
                return $this->setResponse($this->res);
                break;
        }
        $this->res['data'] = [];
        return $this->setResponse($this->res);
    }
}
