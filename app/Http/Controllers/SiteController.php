<?php

namespace App\Http\Controllers;

use App\Models\Ads\Facebook\BotComment;
use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\FacebookVip\VipComment;
use App\Models\Ads\FacebookVip\VipEyes;
use App\Models\Ads\FacebookVip\VipLikeClone;
use App\Models\Ads\Instagram\Instagram;
use App\Models\Ads\Proxy\Proxy;
use App\Models\Ads\Shopee\Shopee;
use App\Models\Ads\TikTok\TikTok;
use App\Models\Ads\Youtube\Youtube;
use App\Models\LogPayment;
use App\Models\Menu;
use App\Models\Prices;
use App\Models\PricesConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    //

    public function getMenu()
    {
        $menu = Menu::where('id', '>', 40)->get();
        $this->res['data'] = $menu;
        return $this->setResponse($this->res);
    }

    public function getAllPrices()
    {
        $prices = Prices::all();
        $this->res['data'] = $prices;
        return $this->setResponse($this->res);
    }

    public function getPricesWithId(Request $request)
    {
        $user = $request->user;
        $this->res['data'] = PricesConfig::where('price_id', $request->price_id)->where('level_id', $user->level)->first();
        return $this->setResponse($this->res);
    }

    public function getListPrice(Request $request)
    {
        $key = $request->header('api-key');
        if ($key) {
            $user = User::where('api_key', $key)->where('status', 1)->first();
        }

        $menu = Menu::where('category_id', '>', 2)->where('status', 1)->get();
        $data = [];
        foreach ($menu as $item) {
            $prices = Prices::select('id', 'name', 'package_name')->where('menu_id', $item->id)->where('active', 1)->where('status', 1)->get();
            if ($user) {
                foreach ($prices as $item_p) {
                    try {
                        $prices_config = PricesConfig::where('price_id', $item_p->id)->where('level_id', $user->level)->where('status', 1)->first();
                        $item_p->price_per = $prices_config->prices ?? -1;
                    } catch (\Exception $exception) {
                    }
                }
            }
            $data_push = [
                'name' => $item->name,
                'path' => $item->path,
                'url_api' => '/api' . $item->path . '/buy',
                'package' => $prices
            ];
            array_push($data, $data_push);
        }
        $this->res['data'] = $data;
        return $this->setResponse($this->res);
    }

    public function history(Request $request)
    {
        if ($request->client_id || $request->user_id_agency_lv2) {
            $menu_id = $request->menu_id ?? 0;
            $client_id = $request->client_id ?? 0;
            switch ($request->type) {
                case 'facebook':
                    $data = Facebook::where('menu_id', $menu_id)->where(function ($q) use ($request) {
                        $key = $request->key;
                        if ($key) {
                            $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
                        }
                        if (isset($request->client_id)) {
                            $client_id = $request->client_id;
                            $q->where('client_id', $client_id);
                        }
                        if (isset($request->user_id_agency_lv2)) {
                            $user_id_agency_lv2 = $request->user_id_agency_lv2;
                            $q->where('user_id_agency_lv2', $user_id_agency_lv2);
                        }
                    })->orderBy('id', 'DESC')->take(50)->get();
                    $this->res['data'] = $data;
                    return $this->setResponse($this->res);
                    break;
                case 'instagram':
                    $data = Instagram::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                        $key = $request->key;
                        if ($key) {
                            $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
                        }
                        if (isset($request->client_id)) {
                            $client_id = $request->client_id;
                            $q->where('client_id', $client_id);
                        }
                        if (isset($request->user_id_agency_lv2)) {
                            $user_id_agency_lv2 = $request->user_id_agency_lv2;
                            $q->where('user_id_agency_lv2', $user_id_agency_lv2);
                        }
                    })->orderBy('id', 'DESC')->take(50)->get();
                    $this->res['data'] = $data;
                    return $this->setResponse($this->res);
                    break;
                case 'youtube':
                    $data = Youtube::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                        $key = $request->key;
                        if ($key) {
                            $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
                        }
                        if (isset($request->client_id)) {
                            $client_id = $request->client_id;
                            $q->where('client_id', $client_id);
                        }
                        if (isset($request->user_id_agency_lv2)) {
                            $user_id_agency_lv2 = $request->user_id_agency_lv2;
                            $q->where('user_id_agency_lv2', $user_id_agency_lv2);
                        }
                    })->orderBy('id', 'DESC')->take(50)->get();
                    $this->res['data'] = $data;
                    return $this->setResponse($this->res);
                    break;
                case 'tiktok':
                    $data = TikTok::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                        $key = $request->key;
                        if ($key) {
                            $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
                        }
                        if (isset($request->client_id)) {
                            $client_id = $request->client_id;
                            $q->where('client_id', $client_id);
                        }
                        if (isset($request->user_id_agency_lv2)) {
                            $user_id_agency_lv2 = $request->user_id_agency_lv2;
                            $q->where('user_id_agency_lv2', $user_id_agency_lv2);
                        }
                    })->orderBy('id', 'DESC')->take(50)->get();
                    $this->res['data'] = $data;
                    return $this->setResponse($this->res);
                    break;
                case 'shopee':
                    $data = Shopee::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                        $key = $request->key;
                        if ($key) {
                            $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
                        }
                        if (isset($request->client_id)) {
                            $client_id = $request->client_id;
                            $q->where('client_id', $client_id);
                        }
                        if (isset($request->user_id_agency_lv2)) {
                            $user_id_agency_lv2 = $request->user_id_agency_lv2;
                            $q->where('user_id_agency_lv2', $user_id_agency_lv2);
                        }
                    })->orderBy('id', 'DESC')->take(50)->get();
                    $this->res['data'] = $data;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_vip_like':
                    $data = VipLikeClone::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                        $key = $request->key;
                        if ($key) {
                            $q->orWhere('fb_id', 'LIKE', '%' . $key . '%');
                        }
                        if (isset($request->client_id)) {
                            $client_id = $request->client_id;
                            $q->where('client_id', $client_id);
                        }
                        if (isset($request->user_id_agency_lv2)) {
                            $user_id_agency_lv2 = $request->user_id_agency_lv2;
                            $q->where('user_id_agency_lv2', $user_id_agency_lv2);
                        }
                    })->orderBy('id', 'DESC')->take(50)->get();
                    $this->res['data'] = $data;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_vip_comment':
                    $data = VipComment::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                        $key = $request->key;
                        if ($key) {
                            $q->orWhere('fb_id', 'LIKE', '%' . $key . '%');
                        }
                        if (isset($request->client_id)) {
                            $client_id = $request->client_id;
                            $q->where('client_id', $client_id);
                        }
                        if (isset($request->user_id_agency_lv2)) {
                            $user_id_agency_lv2 = $request->user_id_agency_lv2;
                            $q->where('user_id_agency_lv2', $user_id_agency_lv2);
                        }
                    })->orderBy('id', 'DESC')->take(50)->get();
                    $this->res['data'] = $data;
                    return $this->setResponse($this->res);
                    break;
                case 'facebook_vip_eyes':
                    $data = VipEyes::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                        $key = $request->key;
                        if ($key) {
                            $q->orWhere('fb_id', 'LIKE', '%' . $key . '%');
                        }
                        if (isset($request->client_id)) {
                            $client_id = $request->client_id;
                            $q->where('client_id', $client_id);
                        }
                        if (isset($request->user_id_agency_lv2)) {
                            $user_id_agency_lv2 = $request->user_id_agency_lv2;
                            $q->where('user_id_agency_lv2', $user_id_agency_lv2);
                        }
                    })->orderBy('id', 'DESC')->take(50)->get();
                    $this->res['data'] = $data;
                    return $this->setResponse($this->res);
                    break;
                case 'proxy':
                    $data = Proxy::where('client_id', $client_id)->where(function ($q) use ($request) {
                        $key = $request->key;
                        if ($key) {
                            $q->orWhere('ip', 'LIKE', '%' . $key . '%');
                        }
                        if (isset($request->client_id)) {
                            $client_id = $request->client_id;
                            $q->where('client_id', $client_id);
                        }
                        if (isset($request->user_id_agency_lv2)) {
                            $user_id_agency_lv2 = $request->user_id_agency_lv2;
                            $q->where('user_id_agency_lv2', $user_id_agency_lv2);
                        }
                    })->orderBy('id', 'DESC')->take(50)->get();
                    $this->res['data'] = $data;
                    return $this->setResponse($this->res);
                    break;
                case 'bot_comment':
                    $data = BotComment::where('menu_id', $menu_id)->where('client_id', $client_id)->where(function ($q) use ($request) {
                        $key = $request->key;
                        if ($key) {
                            $q->orWhere('id', 'LIKE', '%' . $key . '%');
                        }
                        if (isset($request->client_id)) {
                            $client_id = $request->client_id;
                            $q->where('client_id', $client_id);
                        }
                        if (isset($request->user_id_agency_lv2)) {
                            $user_id_agency_lv2 = $request->user_id_agency_lv2;
                            $q->where('user_id_agency_lv2', $user_id_agency_lv2);
                        }
                    })->orderBy('id', 'DESC')->take(50)->get();
                    $this->res['data'] = $data;
                    return $this->setResponse($this->res);
                    break;
                default:
                    return $this->setResponse($this->res);
                    break;
            }
        } else {
            abort(404);
        }
    }

    public function logsOrder(Request $request)
    {
        switch ($request->type) {
            case 'facebook':
                $data = Facebook::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                    if (isset($request->id)) {
                        $q->where('id', $request->id);
                    }
                    $client_id = $request->client_id;
                    if ($client_id) {
                        $q->where('client_id', $client_id);
                    }
                    if ($request->list_ids) {
                        $list_id = explode(",", $request->list_ids);
                        $q->whereIn('id', $list_id);
                    }
                })->orderBy('id', 'DESC')->take(5000)->get();
                break;
            case 'instagram':
                $data = Instagram::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                    if (isset($request->id)) {
                        $q->where('id', $request->id);
                    }
                    $client_id = $request->client_id;
                    if ($client_id) {
                        $q->where('client_id', $client_id);
                    }
                    if ($request->list_ids) {
                        $list_id = explode(",", $request->list_ids);
                        $q->whereIn('id', $list_id);
                    }
                })->orderBy('id', 'DESC')->take(5000)->get();

                break;
            case 'youtube':
                $data = Youtube::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                    if (isset($request->id)) {
                        $q->where('id', $request->id);
                    }
                    $client_id = $request->client_id;
                    if ($client_id) {
                        $q->where('client_id', $client_id);
                    }
                    if ($request->list_ids) {
                        $list_id = explode(",", $request->list_ids);
                        $q->whereIn('id', $list_id);
                    }
                })->orderBy('id', 'DESC')->take(5000)->get();
                break;
            case 'tiktok':
                $data = TikTok::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                    if (isset($request->id)) {
                        $q->where('id', $request->id);
                    }
                    $client_id = $request->client_id;
                    if ($client_id) {
                        $q->where('client_id', $client_id);
                    }
                    if ($request->list_ids) {
                        $list_id = explode(",", $request->list_ids);
                        $q->whereIn('id', $list_id);
                    }
                })->orderBy('id', 'DESC')->take(5000)->get();
                break;
            case 'shopee':
                $data = Shopee::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                    if (isset($request->id)) {
                        $q->where('id', $request->id);
                    }
                    $client_id = $request->client_id;
                    if ($client_id) {
                        $q->where('client_id', $client_id);
                    }
                    if ($request->list_ids) {
                        $list_id = explode(",", $request->list_ids);
                        $q->whereIn('id', $list_id);
                    }
                })->orderBy('id', 'DESC')->take(5000)->get();
                break;
            case 'facebook_vip_like':
                $data = VipLikeClone::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                    if (isset($request->id)) {
                        $q->where('id', $request->id);
                    }
                    $client_id = $request->client_id;
                    if ($client_id) {
                        $q->where('client_id', $client_id);
                    }
                    if ($request->list_ids) {
                        $list_id = explode(",", $request->list_ids);
                        $q->whereIn('id', $list_id);
                    }
                })->orderBy('id', 'DESC')->take(5000)->get();
                break;
            case 'facebook_vip_comment':
                $data = VipComment::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                    if (isset($request->id)) {
                        $q->where('id', $request->id);
                    }
                    $client_id = $request->client_id;
                    if ($client_id) {
                        $q->where('client_id', $client_id);
                    }
                    if ($request->list_ids) {
                        $list_id = explode(",", $request->list_ids);
                        $q->whereIn('id', $list_id);
                    }
                })->orderBy('id', 'DESC')->take(5000)->get();
                break;
            case 'facebook_vip_eyes':
                $data = VipEyes::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                    if (isset($request->id)) {
                        $q->where('id', $request->id);
                    }
                    $client_id = $request->client_id;
                    if ($client_id) {
                        $q->where('client_id', $client_id);
                    }
                    if ($request->list_ids) {
                        $list_id = explode(",", $request->list_ids);
                        $q->whereIn('id', $list_id);
                    }
                })->orderBy('id', 'DESC')->take(5000)->get();
                break;
            case 'proxy':
                $data = Proxy::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                    if (isset($request->id)) {
                        $q->where('id', $request->id);
                    }
                    $client_id = $request->client_id;
                    if ($client_id) {
                        $q->where('client_id', $client_id);
                    }
                    if ($request->list_ids) {
                        $list_id = explode(",", $request->list_ids);
                        $q->whereIn('id', $list_id);
                    }
                })->orderBy('id', 'DESC')->take(5000)->get();
                break;
            case 'bot_comment':
                $data = BotComment::where('user_id', $request->user->id)->where(function ($q) use ($request) {
                    if (isset($request->id)) {
                        $q->where('id', $request->id);
                    }
                    $client_id = $request->client_id;
                    if ($client_id) {
                        $q->where('client_id', $client_id);
                    }
                    if ($request->list_ids) {
                        $list_id = explode(",", $request->list_ids);
                        $q->whereIn('id', $list_id);
                    }
                })->orderBy('id', 'DESC')->take(5000)->get();
                break;
            default:
                return $this->setResponse($this->res);
                break;
        }
        $z = [];
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
            if ($item->status == 3) {
                $x = "refund";
            }
            $z[$i] = (object)[
                'id' => $item->id,
                'start_like' => $item->start_like,
                'status' => $x,
                'count_is_run' => $item->count_is_run,
                'object_id' => $item->object_id,
                'quantity' => $item->quantity,
                'prices' => $item->prices,
                'price_per' => $item->price_per,
            ];
        }
        $this->res['data'] = $z;
        $this->res['count'] = count($z);
        return $this->setResponse($this->res);
    }

    public function index(Request $request)
    {
        if (!Auth::user()->api_key) {
            $user = User::find(Auth::user()->id);
            $user->api_key = base64_encode($user->id . strtolower('now') . str_rand('25'));
            $user->save();
        }
        return view('Site.index');
    }

    public function getMe(Request $request)
    {
        if ($request->api_key) {
            $user = User::where('api_key', $request->api_key)->first();
            if ($user) {
                $this->res['data'] = $user;
                return $this->setResponse($this->res);
            } else {
                $this->res['message'] = "Key sai";
                $this->res['status'] = 400;
                $this->res['success'] = false;
                return $this->setResponse($this->res);
            }
        }
        $this->res['message'] = "Key sai !";
        $this->res['status'] = 400;
        $this->res['success'] = false;
        return $this->setResponse($this->res);
    }

    public function logsCard(Request $request)
    {
        if ($request->client_id) {
            $data = LogPayment::where('client_user_id', $request->client_id)->take(100)->get();
            $this->res['data'] = $data;
        }
        if ($request->user_id_agency_lv2) {
            $data = LogPayment::where('user_id_agency_lv2', $request->user_id_agency_lv2)->take(100)->get();
            $this->res['data'] = $data;
        }
        return $this->setResponse($this->res);
    }
}
