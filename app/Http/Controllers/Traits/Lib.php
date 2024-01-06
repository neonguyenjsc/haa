<?php

namespace App\Http\Controllers\Traits;

use App\Models\Ads\Facebook\BotComment;
use App\Models\Ads\Facebook\Eyes;
use App\Models\Ads\Facebook\Facebook;
use App\Models\Ads\Facebook\FacebookLike;
use App\Models\Ads\FacebookVip\VipLikeClone;
use App\Models\Ads\Instagram\Instagram;
use App\Models\Ads\Instagram\InstagramVipLike;
use App\Models\Ads\Other\PaymentCard;
use App\Models\Ads\Proxy\Proxy;
use App\Models\Ads\Shopee\Shopee;
use App\Models\Ads\Telegram\PostView;
use App\Models\Ads\TikTok\TikTok;
use App\Models\Ads\Youtube\Youtube;
use App\Models\Config;
use App\Models\Prices\Prices;
use App\Models\Prices\PricesConfig;
use App\Models\TokenCheck;
use App\Models\User;
use App\Service\Telegram\TelegramService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait Lib
{

    protected $res = [
        'status' => 200,
        'data' => [],
        'error' => [],
        'success' => true,
        'message' => ''
    ];

    public function sendMessGroupCardToBotTelegram($mess)
    {
        $curl = $this->curl('https://api.telegram.org/bot1490265752:AAGfPdahlHJOBS9_eC3AHj_Lm0QWqUxovbs/sendMessage?chat_id=-320383582&text=' . urlencode($mess));
        return $curl;
    }

    protected function validateMessage()
    {
        return [];
    }

    public function convertUIDIg($link, $type = 'like')
    {
        $link = getUrlReplaceString($link);
        if (strpos($link, "instagram.com")) {
            if ($type == 'like' || $type == 'view') {
                try {
                    preg_match('/(http[s]?:\/\/)?([^\/\s]+\/)(.\/)(.*)\//', $link, $post_id);
                    return str_replace("/", "", $post_id[4]);
                } catch (\Exception $exception) {
                    return false;
                }
            }
            if ($type == 'follow' || $type == 'sub') {
                $profile_id = explode("/", $link);
                try {
                    return str_replace("/", "", $profile_id[3]);
                } catch (\Exception $exception) {
                    return false;
                }

            }
        } else {
            return false;
        }
    }

    public function checkStartV2($object_id, $type)
    {
//        $this->sendError500ToTelegram($type);

        $getToken = TokenCheck::all();
        if (count($getToken) > 0) {
            //$token = 'EAAGNO4a7r2wBAE8QZBmecnZBlh6b9ZBqRsK9DSqwHI5ZCojqZCHYZBlhHsxB5Kz1wFfyPtwMRo4ZA4aNBZCkXPWIM4xXe6kSA3sz1ZBRZC2VzSJaBxxMvZCmFWaOVhpGZAneGyo8hhmZC6ZA2VrLL5F7AIHmNwjiCCbXL6qZBJ1ZAICIcwqHBWAJgLykc3rh';
            foreach ($getToken as $item_token) {
                $token = str_replace("\n", "", $item_token->value);
                if ($type == 'follow') {
                    $url = "https://graph.facebook.com/$object_id?fields=id,name,subscribers.limit(0)&access_token=" . $item_token->value;
                    $data = $this->curlFb($url);
                    $data = json_decode($data);
                    if (isset($data->subscribers->summary->total_count)) {
                        $start = $data->subscribers->summary->total_count;
                        return intval($start);
                    } else {
                        if (isset($data->error->code) && ($data->error->code == 190)) {
                            $item_token->delete();
                            continue;
                        } elseif (!isset($data)) {
                        } elseif (isset($data->error->code) && ($data->error->code == 368)) {
                            continue;
                        } elseif (isset($data->error->code) && ($data->error->code == 1)) {
                            $item_token->delete();
                            continue;
                        } else {
                            return ['error' => 'Không thể kiểm tra thông tin. Hãy chắc rằng page bạn đã công khai'];
                        }
                    }
                } elseif ($type == 'like_page') {
                    $url = 'https://graph.facebook.com/v8.0/' . $object_id . '?fields=id,name,fan_count,rating_count&access_token=' . $token;
                    $data = $this->curlFb($url);
                    $data = json_decode($data);
                    if (isset($data->fan_count)) {
                        $start = $data->fan_count;
                        return intval($start);
                    } else {
                        if (isset($data->error->code) && ($data->error->code == 190)) {
                            $item_token->delete();
                            continue;
                        } elseif (!isset($data)) {
                        } elseif (isset($data->error->code) && ($data->error->code == 368)) {
                            continue;
                        } elseif (isset($data->error->code) && ($data->error->code == 1)) {
                            $item_token->delete();
                            continue;
                        } else {
                            return ['error' => 'Không thể kiểm tra thông tin. Hãy chắc rằng page bạn đã công khai'];
                        }
                    }
                } else {
                    return ['error' => 'Không thể kiểm tra thông tin. Vui lòng liên hệ admin'];
                }
            }
        }
        return ['error' => 'Không thể kiểm tra thông tin vui lòng liên hệ admin!!'];
    }

//    public function curlFb($url)
//    {
//        $proxy = $this->getProxy();
////        $url = $url . $token;
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
////            CURLOPT_URL => 'https://graph.facebook.com/100005048257446?fields=id,name,subscribers.limit(0)&access_token=EAAGNO4a7r2wBAE8QZBmecnZBlh6b9ZBqRsK9DSqwHI5ZCojqZCHYZBlhHsxB5Kz1wFfyPtwMRo4ZA4aNBZCkXPWIM4xXe6kSA3sz1ZBRZC2VzSJaBxxMvZCmFWaOVhpGZAneGyo8hhmZC6ZA2VrLL5F7AIHmNwjiCCbXL6qZBJ1ZAICIcwqHBWAJgLykc3rh',
//            CURLOPT_URL => $url,
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'GET',
////            CURLOPT_PROXY => $proxy->ip . ':' . $proxy->port,
////            CURLOPT_PROXYUSERPWD => $proxy->username . ':' . $proxy->password,
//            CURLOPT_PROXY => $this->getPoxyV4(),
////            CURLOPT_PROXYUSERPWD => 'user49091:VFOUkUvToC',
//        ));
//
//        $response = curl_exec($curl);
//        try {
//            $t = new TelegramService();
//            $t->sendMessGroupCardToBotTelegram("check_start =>" . $response);
//        } catch (\Exception $exception) {
//        }
//        curl_close($curl);
//        return $response;
//    }
    public function curlFb($url)
    {
        $proxy = $this->getProxy();
        $data = [
            'url' => $url,
            'ip' => $proxy->ip . ':' . $proxy->port,
            'auth' => $proxy->username . ':' . $proxy->password,
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => '45.77.43.222/api/check-start',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        try {
            $t = new TelegramService();
            $t->sendMessGroupCardToBotTelegram("check_start =>" . $response);
        } catch (\Exception $exception) {
        }
        curl_close($curl);
        return $response;
    }

    public function getPoxyV4()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://dash.minproxy.vn/api/rotating/v1/proxy_v4/get-new-proxy?api_key=yUmePdmx02OgjQ5NXxYE4jtzHRZaIsxV',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Connection: keep-alive',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36',
                'Accept: */*',
                'Sec-Fetch-Site: none',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Dest: empty',
                'Accept-Language: en-US,en;q=0.9',
                'Cookie: cf_clearance=BvX9zTgvtKg15Gyk2YrCErhwmAWPV4RPDLlgfnTvlgg-1648201585-0-150; XSRF-TOKEN=eyJpdiI6IjJKMDNiU1BcL3NhY2dcL0xjTTAwV1hadz09IiwidmFsdWUiOiJPd0hOZDVHVU9kMG9RM0dqQ1NvUUd2MGgxN0V4eDR3VWFjczVhUmhqdkUwR1FTYmxVYlA0aHduU2l6RjJ2RGsxdzdBMFk0MXVjN296SzFBQUdhejAxK2ZzMmRyaThCT1UwN2V4Nk5EWVNQc25EclRRWDBkdll6M3d3cHl0bzZuVyIsIm1hYyI6IjZlNTk1ZGI3OGY0Mzg0Y2Y4YmRhZmNiYTg1MmFhNzZlNjNhYTQ4NjczODE5YWYwYjUwY2ZlMDNkMjZkMzlhMWMifQ%3D%3D; laravel_session=eyJpdiI6Ik8xZWRndHVwNENxTm4xZWFJdVpGNXc9PSIsInZhbHVlIjoiM2FIRzFwSll6bmw2UFUyS21oUkdMTmptV1gzdkg2WVwvZTk2cmd4akVyRXZlbnJCY0ZzMGFSbEJBY3BjUkZ0cGwzK2p6VTRqK0hcL2owb1ZyS202WEx1bkR1MENXbyttVGFwNFwvT0ZrajdOYnRvdVpoXC9sVEZIY2xRUXFZc2RaMm5NIiwibWFjIjoiNTAxNjAxMTUwYjEwNzFmNDNlMjhjNWI0MWM4NGExNWRmZjlmOWRkNDQzNzRkM2FjNDk1YmQ0MWIxYWY5NmIyZSJ9'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        $proxy = $response->data->http_proxy_ipv4;
        return $proxy;
    }

    public function getProxy()
    {
        $proxy = \App\Models\Proxy::inRandomOrder()->first();
        return $proxy;
    }

    public function setResponse($data)
    {
        if (isset($data['type'])) {
            if ($data['type'] == false) {
                $this->res['success'] = false;
                unset($this->res['type']);
            }
        }
        if (isset($data['data']) && $data['data']) {
            $this->res['data'] = $data['data'];
        }
        if (isset($data['token'])) {
            $this->res['token'] = $data['token'];
        }
        if (isset($data['log'])) {
            $this->res['log'] = $data['log'];
        }
        if (isset($data['recheck_otp'])) {
            $this->res['recheck_otp'] = $data['recheck_otp'];
        }
        if (isset($data['message'])) {
            $this->res['message'] = $data['message'];
        }
        if (isset($data['error']) && $data['error']) {
            $this->res['error'] = $data['error'];
            $this->res['success'] = false;
        }
        if (isset($data['status'])) {
            $this->res['status'] = $data['status'];
        }
        if (isset($data['status']) && $data['status'] >= 400) $this->res['success'] = false;
        if ($this->res['success'] == false) {
            $this->res['status'] = 400;
        }
        return response()->json($this->res, $this->res['status'], []);
    }

    public function addDaysWithDate($date, $days)
    {
        $date = strtotime("+" . $days . " days", strtotime($date));
        return date("Y-m-d H:i:s", $date);
    }

    public function addMinuteWithDate($date, $minutes)
    {
        $date = strtotime('+' . $minutes . ' minutes', strtotime($date));
        return date("Y-m-d H:i:s", $date);
    }

    public function addMonthsWithDate($date, $month)
    {
        $date = strtotime("+" . $month . " months", strtotime($date));
        return date("Y-m-d H:i:s", $date);
    }

    protected function buildQueryModel($model, $unset = [])
    {
        $date_from = $date_to = false;
        $requestData = request()->all();
        $modelClone = clone $model;
        $modelObject = $modelClone->first();
        if ($modelObject) {
            if (in_array('toArray', get_class_methods($modelObject))) {
                $keyExist = array_keys($modelObject->toArray());
            }
        }
        if (strtolower(request()->getMethod()) == 'get') $requestData = request()->query();
        if (array_key_exists('date_from', $requestData) && !is_null($requestData['date_from'])) $date_from = new \Carbon\Carbon($requestData['date_from']);
        if (array_key_exists('date_to', $requestData) && !is_null($requestData['date_to'])) $date_to = new \Carbon\Carbon($requestData['date_to']);
        foreach ($requestData as $key => $item) {
            if (!in_array($key, $unset) && (isset($keyExist) && in_array($key, $keyExist)) && !in_array($key, ['page', 'limit', 'order_by', 'sort_by', 'date_from', 'date_to'])) {
                if (!is_null($item)) $model->where(trim($key), $item);
            }
            if ($date_from && $date_to) $model = $model->whereBetween('created_at', array($date_from, $date_to));
            elseif ($date_to) $model = $model->where('created_at', '<', $date_to);
            elseif ($date_from) $model = $model->where('created_at', '>=', $date_from);
        }
        return $model;
    }

    public function countDateDiff($from, $to)
    {
        $to = strtotime($to);
        $from = strtotime($from);
        $date_diff = $to - $from;
        return round($date_diff / (60 * 60 * 24));
    }

    protected function checkCoinUser($user_id, $check = 1)
    {
        $user = User::where('id', $user_id)->first();
        if ($user->coin < 1) {
            return false;
        }
        if ($user->coin < $check) {
            return false;
        }
        return true;
    }

    protected function handleCoinUser($user_id, $coin)
    {
        $user = User::where('id', $user_id)->lockForUpdate()->first();
        if ($coin > 0) {
            if ($user->coin < 1) {
                return false;
            }
            $new_coin = intval($user->coin) - $coin;
            if ($new_coin >= 0) {
                DB::beginTransaction();
                try {
                    $user->coin = $new_coin;
                    $user->save();
                    DB::commit();
                    return true;
                } catch (\Exception $exception) {
                    DB::rollback();
                    return false;
                }
            }
        }
        return false;
    }

    protected function sumCoin($user_id, $coin)
    {
        $user = User::where('id', $user_id)->lockForUpdate()->first();
        if ($coin > 0) {
            $new_coin = intval($user->coin) + $coin;
            if ($new_coin >= 0) {
                DB::beginTransaction();
                try {
                    $user->coin = $new_coin;
                    $user->save();
                    DB::commit();
                    return true;
                } catch (\Exception $e) {
                    DB::rollback();
                    return false;
                }
            }
        }
        return false;
    }

    function sumTotalUse($user_id, $coin)
    {
        $user = User::where('id', $user_id)->lockForUpdate()->first();
        if ($coin > 0) {
            if ($user->coin < 0) {
                return false;
            }
            $new_coin = intval($user->total_use) + $coin;
            if ($new_coin >= 0) {
                try {
                    $user->total_use = $new_coin;
                    $user->save();
                    return true;
                } catch (\Exception $exception) {
                    return false;
                }
            }
        }
        return false;
    }

    public function getPostIdFromFacebookUrl($url, $type = false)
    {
        if (strpos($url, "facebook.com")) {
            if (!in_array($type, ['sub', 'follow_dx', 'follow', 'like_page',
                'follow_corona', 'follow_corona_5', 'follow_corona_10',
                'like_page_corona', 'like_page_corona_5', 'like_page_corona_10',
            ])) {
                // match post_id
                preg_match('/(.*)\/posts\/([0-9]{8,})/', $url, $post_id);
                // match photo_id
                preg_match('/(.*)\/photo.php\?fbid=([0-9]{8,})/', $url, $photo_id);
                // match video_id
                preg_match('/(.*)\/video.php\?v=([0-9]{8,})/', $url, $video_id);
                // match photo https://www.facebook.com/photo?fbid=2259354400898058&set=a.103741726459347
                preg_match('/(.*)\?fbid=([0-9]{8,})/', $url, $photo_id1);
                // store Id
                preg_match('/(.*)\/story.php\?story_fbid=([0-9]{8,})/', $url, $store_id);
                preg_match('/(.*)\/story.php\?story_fbid=(.*)\&id=/', $url, $store_id_v2);
                // match link_id
                preg_match('/(.*)\/permalink.php\?story_fbid=([0-9]{8,})/', $url, $link_id);
                // match media
                preg_match('/(.*)\/set\/\?set=a\.([0-9]{8,})/', $url, $media_id);
                // match other_id
                preg_match('/(.*)\/([0-9]{8,})/', $url, $other_id);
                // comment Id
                preg_match('/(.*)\/([0-9]{8,})/', $url, $comment_id);
                if (!empty($post_id)) {
                    return $post_id[2];
                }
                if (!empty($photo_id)) {
                    return $photo_id[2];
                }
                if (!empty($photo_id1)) {
                    return $photo_id1[2];
                }
                if (!empty($photo_id2)) {
                    return $photo_id2[2];
                }
                if (!empty($video_id)) {
                    return $video_id[2];
                }
                if (!empty($link_id)) {
                    return $link_id[2];
                }
                if (!empty($store_id)) {
                    return $store_id[2];
                }
                if (!empty($store_id_v2)) {
                    return $store_id_v2[2];
                }
                if (!empty($other_id)) {
                    return $other_id[2];
                }
                if (!empty($comment_id)) {
                    return '_' . $comment_id[2];
                }
                if (!empty($media_id)) {
                    return $media_id[2];
                }
            } else {
                $object_id = $url;
                if (strpos($object_id, "profile.php?id=")) {
                    //(.*)\/(.*)\/(profile.php)\?id=([0-9]{8,})
                    preg_match('/(.*)\/(.*)\/(profile.php)\?id=([0-9]{8,})/', $url, $profile_id);

                    $object_id = $profile_id[4];
                } else {
                    preg_match('/(.*)\/(.*)/', $url, $profile_id);
                    $object_id = $profile_id[2];
                }
                return $object_id;
            }
        }
        return $url;
    }

    public function getUIdFromUserName($username)
    {
        $response = $this->callApi([
            'username' => $username
        ], 'https://api.findids.net/api/get-uid-from-username');
        if ($response && isset($response['data']['id'])) {
            return $response['data']['id'];
        }
        return false;
    }

    protected function callApi($post_data, $url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($post_data)
        ));
        $response = curl_exec($ch);
        if ($response === FALSE) {
            return false;
        }
        return json_decode($response, TRUE);
    }

    public function curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function curlToMrDark($url, $post_data, $method = 'POST')
    {
        $token = Config::where('alias', 'key_tanglikecheo')->first();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POSTREDIR, 3);
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($post_data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($post_data)
                    $url = sprintf("%s?%s", $url, http_build_query($post_data));
        }
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer " . trim($token->value),
            't: ' . time()
        ]);

        $result = curl_exec($curl);

        if ($result === FALSE) {
            return false;
        }
        //$this->sendMessToBotTelegram($result);

        curl_close($curl);

        return json_decode($result);
    }

    public function sendBotTelegram($mes)
    {
        return $this->curl('https://api.telegram.org/' . LIKEGIARE_DEBUG_BOT . '/sendMessage?chat_id=983738766&text=' . urlencode($mes));

    }

    public function getHistory($modal, $menu_id, $request)
    {
        $limit = $request->limit ?? 10;
        $key = $request->key;
        if (($key == 'tanglikegiare' && $modal != 'facebook') || ($key == 'tanglikegiare' && $menu_id == 45)) {
            $key = 'ta123nglikegiare123';
        }
        if (Auth::user()->role == 'admin') {
            $data = $this->getModel($modal)::where('menu_id', $menu_id)->where(function ($q) use ($request, $key) {
                if ($key) {
                    $q->where('username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('client_username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
                    $q->orWhere('id', 'LIKE', '%' . $key . '%');
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
            })->orderBy('id', 'DESC')->paginate($limit);
        } else {
            $data = $this->getModel($modal)::where('user_id', Auth::user()->id)->where('menu_id', $menu_id)->where(function ($q) use ($request) {
                $key = $request->key;
                if ($key) {
//                    $q->where('username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('client_username', 'LIKE', '%' . $key . '%');
                    $q->orWhere('object_id', 'LIKE', '%' . $key . '%');
                    $q->orWhere('id', 'LIKE', '%' . $key . '%');
                }
            })->orderBy('id', 'DESC')->paginate($limit);
        }
        return $data;
    }

    public function getModel($modal)
    {
        $class = [
            'facebook' => Facebook::class,
            'facebook_eyes' => Eyes::class,
            'facebook_vip_clone' => VipLikeClone::class,
            'instagram' => Instagram::class,
            'tiktok' => TikTok::class,
            'shopee' => Shopee::class,
            'youtube' => Youtube::class,
            'proxy' => Proxy::class,
            'bot-comment' => BotComment::class,
            'instagram-vip-like' => InstagramVipLike::class,
            'card' => PaymentCard::class,
            'telegram' => PostView::class,
        ];
        return $class[$modal] ?? Facebook::class;
    }

    public function callAutoCC($data, $url)
    {
        $config_token = Config::where('alias', 'key_auto_like_cc')->first();
        $data_config_token = json_decode($config_token->value);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'authority: api-autolike.congaubeo.us',
                'accept: application/json',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
                'token: ' . $data_config_token->token ?? '',
                'agency-secret-key: ' . $data_config_token->agency_secret_key ?? '',
                'content-type: application/json',
                'origin: https://www.mottrieu.com',
                'sec-fetch-site: cross-site',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://www.mottrieu.com/',
                'accept-language: vi',
                'Cookie: __cfduid=d6f82b0ecbbd5fcdc3d82712dfa53082e1609122016'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function returnActionWeb($result)
    {
        if (isset($result['success'])) {
            $this->res['status'] = 200;
            $this->res['message'] = $result['success'];
            return redirect()->back()->with(['success' => $this->res['message']]);
        }
        if (isset($result['error'])) {
            $this->res['status'] = 422;
            $this->res['message'] = $result['error'];
            $this->res['error'] = $result['error'];
            return redirect()->back()->with(['error' => $result['error']]);
        }
        if (isset($result['error___'])) {
            $this->res['status'] = 422;
            $this->res['error___'] = $result['error___'];
            return redirect()->back()->with(['error___' => $result['error___']]);
        }
        if (isset($result['error_'])) {
            $this->res['status'] = 400;
            $this->res['message'] = $result['error_'];
            return redirect()->back()->with(['error_' => $result['error_']]);
        }
        $this->res['status'] = 400;
        $this->res['message'] = "Mua thất bại";
        return redirect()->back()->with(['error_' => $this->res['message']]);
    }

    public function checkAllowApi($request)
    {
        if (!isset($request->client_id)) {
            return false;
        }
        return true;
    }


    public function returnActionApi($result)
    {
        $this->res['hold_coin'] = $result['hold'] ?? false;
        $this->res['status'] = 400;
        $this->res['message'] = "Mua thất bại";
        $this->res['data'] = $result['data'] ?? [];
        if (isset($result['success'])) {
            $this->res['status'] = 200;
            $this->res['message'] = $result['success'];
            return $this->setResponse($this->res);
        }
        if (isset($result['error'])) {
            $data = $result['error']->toArray();
            $message = 'Dữ gửi lên không hợp lệ';
            foreach ($data as $i => $item) {
                if (isset($item[0])) {
                    $message = $item[0];
                }
            }
            $this->res['status'] = 422;
            $this->res['message'] = $message;
            $this->res['error'] = $result['error'];
            return $this->setResponse($this->res);
        }
        if (isset($result['error___'])) {
            $this->res['status'] = 422;
            $this->res['error___'] = $result['error___'];
            return $this->setResponse($this->res);
        }
        if (isset($result['error_'])) {
            $this->res['status'] = 400;
            $this->res['message'] = $result['error_'];
            return $this->setResponse($this->res);
        }

        return $this->setResponse($this->res);
    }

    public function checkPricePer($price_per, $price_min)
    {
        if ($price_per >= $price_min) {
            return true;
        }
        return false;
    }

    public function checkMinMax($quantity, $prices)
    {
        if (isset($prices->min) && isset($prices->max)) {
            if ($quantity >= $prices->min && $quantity <= $prices->max) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function getPriceMasterMfb($data)
    {
        $url = DOMAIN_MFB . '/api/tools/price';
        $response = $this->callApiMfb($data, $url, 'GET');
        if (isset($response->data) && $response->data > 0) {
            return $response->data;
        }
        return false;

    }

    public function checkCoinAndHandleCoin($user_id, $coin)
    {
        if ($this->checkCoinUser($user_id, $coin)) {
            if ($this->handleCoinUser($user_id, $coin)) {
                return true;
            } else {
                return ['error' => 'Không đủ tiền trong ví vui lòng nạp thêm!!!!!'];
            }
        } else {
            return ['error' => 'Không đủ tiền trong ví vui lòng nạp thêm !'];
        }
    }


    public function callApiMfb($data, $url, $method = 'POST', $timeout = 3000)
    {
        $token = DB::table('config')->where('alias', 'key_mfb')->first();
        $curl = curl_init();

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . trim($token->value ?? ''),
        ));

        curl_setopt($curl, CURLOPT_POSTREDIR, 3);


        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        if ($result === FALSE) {
            return false;
        }

        curl_close($curl);

        return json_decode($result);
    }

    public function callApiTLC($data, $url, $method = 'POST', $timeout = 3000)
    {
        $token = DB::table('system_config')->where('alias', 'key_tlc')->first();
        $curl = curl_init();

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . trim($token->value ?? ''),
            "t: " . strtotime(date('Y-m-d H:i:s'))
        ));

        curl_setopt($curl, CURLOPT_POSTREDIR, 3);


        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        if ($result === FALSE) {
            return false;
        }

        curl_close($curl);

        return json_decode($result);
    }

    public function callAutoLikeCC($data, $url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "authority: us-central1-autolike-cc-f0644.cloudfunctions.net",
                "accept: application/json",
                "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36",
                "content-type: application/json",
                "origin: https://autolike.cc",
                "sec-fetch-site: cross-site",
                "sec-fetch-mode: cors",
                "sec-fetch-dest: empty",
                "referer: https://autolike.cc/",
                "accept-language: en-US,en;q=0.9"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function getPricesTLC($type)
    {
        $url = DOMAIN_TANGLIKECHEO . '/api/prices';
        $response = $this->callApiTLC([], $url, 'GET');
        $data = $response;
        if (isset($data->data->data->config->$type)) {
            return $data->data->data->config->$type;
        } else {
            return ['error' => 'Hệ thống không thể cập nhật giá cho bạn vui lòng liên hệ admin !'];
        }
    }

    public function curlToSbooks($data)
    {
        $url = DOMAIN_SBOOKS;
        $url = sprintf("%s?%s", $url, http_build_query($data));
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: __cfduid=d6225a0008af9b8c788e8762f943753421608041428; PHPSESSID=24e6e6edcb4e01e5d25c6d0609806012'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);

    }

    public function increaseOrder($user_id)
    {
        $user = User::find($user_id);
        $user->total_orders = $user->total_orders + 1;
        $user->save();
    }

    public function returnValidateResponseApi($response)
    {
        if (is_array($response->error) && isset($response->error[0]) && reset($response->error)) {
            $error_ = reset($response->error);
            return $error_[0] ?? 'Tạo đơn thất bại vui lòng thử lại sau';
        } elseif (is_string($response->error)) {
            return $response->error;
        }
        return false;
    }

    public function callApiViewYT($data)
    {
        $key = Config::where('alias', 'key_viewyt')->first();
        $data['key'] = $key->value ?? '';
        $data['action'] = 'add';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://viewyt.com/api/v2",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function callBuffViewer($data)
    {
        $url = DOMAIN_BUFFVIEWER . '/api/orderlivestreamunit/add';
        $config = Config::where('alias', 'api_viewer')->first();
        if ($config) {
            $curl = curl_init();

            $header = array(
                "authorization:" . $config->value,
                "language:vi",
                "content-type:application/json"
            );


            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 180,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => $header,
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return false;
            } else {
                return json_decode($response);
            }
        } else {
            return false;
        }
    }

    public function curlToTangLikeOrg($data)
    {
        $token = Config::where('alias', 'key_tanglikeorg')->first();
        $curl = curl_init();
        $id = $data['object_id'];
        $note = $data['notes']; // ghi chú có thể bỏ trống
        $soluong = $data['quantity'];
        $type = $data['type'];
        $modun = $data['modun'];
        $tokensite = $token->value ?? '';

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://tanglike.org/2T_modun/modun_post.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "t=buy-buff-fb&id=$id&note=$note&soluong=$soluong&type=$type&link=&modun=$modun&tokensite=$tokensite",
            CURLOPT_HTTPHEADER => array(
                "authority: tanglike.org",
                "accept: application/json, text/javascript, */*; q=0.01",
                "x-requested-with: XMLHttpRequest",
                "user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.135 Safari/537.36",
                "content-type: application/x-www-form-urlencoded; charset=UTF-8",
                "origin: https://tanglike.org",
                "sec-fetch-site: same-origin",
                "sec-fetch-mode: cors",
                "sec-fetch-dest: empty",
                "referer: https://tanglike.org/?act=buff-share-group",
                "accept-language: vi,en-US;q=0.9,en;q=0.8",
                "Cookie: __cfduid=d6090d9cd8c52dd97651fa959ebfb3cd01597767144; PHPSESSID=207bd52eed51fb9e863bb4a0af6c90e1"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $decoded = json_decode($response);
        return $decoded;
    }

    public function convertUidYoutube($link, $type = 'video')
    {
        switch ($type) {
            case 'video':
                echo "<pre>";
                print_r($value = $link);
                echo "</pre>";
                exit();
                break;
        }
        return $link;
    }

    public function callOrderToTelegram($data)
    {
        $txt = '';
        foreach ($data as $i => $item) {
            $txt = $txt . " - $i : $item \n";
        }
        return $this->curl('https://api.telegram.org/' . LIKEGIARE_DEBUG_BOT . '/sendMessage?chat_id=-517298130&text=' . urlencode($txt));
    }

    public function callToAgencyByAdmin($data, $url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'key-admin-master: NMVacglb5FuwnJqXRbGa',
                'key-master: ' . KEY_MASTER,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function sendToTelegramId($mess, $id)
    {
        return $this->curl('https://api.telegram.org/bot5417742397:AAF3-0UvL5Rwqad7gh8liYFJPpsCIgKaHdc/sendMessage?chat_id=' . $id . '&text=' . urlencode($mess));
    }

    public function convertUidTikTok($link, $type = 'follow')
    {
        if ($type == "follow") {
            $data = explode("@", $link);
            return $data[1] ?? $link;
        }
        return $link;
    }
}
