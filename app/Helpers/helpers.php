<?php

use App\Models\PaymentMonth;
use Illuminate\Support\Facades\Auth;

define('MY_IP', '2402:800:63b6:d5e9:d8c9:6baf:279b:bd37');
define('MY_IP_CT', '171.227.247.25');
define('DOMAIN_GACHTHECAO', '66.42.51.127');
define('DOMAIN_SBOOKS', 'https://sbooks.me/api/add_sub');
define('AUTO_LIKE', 'https://api-autolike.congaubeo.us');
//define('DOMAIN_VNKINGS', 'https://vnkings.net/api/v2');
define('DOMAIN_TANGLIKE_ORG', 'https://tanglike.org');

function getPricesMin($level, $package_name)
{
    $ey = 'price_min_history' . $level . $package_name;
    return \Illuminate\Support\Facades\Cache::remember($ey, 3600, function () use ($level, $package_name) {
        $prices = \App\Models\PricesConfig::where('package_name', $package_name)->where('level_id', $level)->first();
        return $prices->prices ?? 0;
    });
}

function getUrlReplaceString($link, $need = '?')
{
    if (strpos($link, $need) > -1) {
        return strstr($link, $need, true);
    }
    return $link;
}

function getTdStatus($s)
{
    if ($s) {
        return "<td class='text-success'>Hoạt động</td>";
    } else {
        return "<td class='text-danger'>Khóa</td>";
    }
}

function startCron($key)
{
    if (\Illuminate\Support\Facades\DB::table('cron')->where('key', $key)->where('status', 0)->first()) {
        \Illuminate\Support\Facades\DB::table('cron')->where('key', $key)->update(['status' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
        return true;
    }
    return false;
}

function endCron($key)
{
    \Illuminate\Support\Facades\DB::table('cron')->where('key', $key)->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    return true;
}

function getConfig($key)
{
    $c = \App\Models\Config::where('alias', $key)->first();
    return $c->value;
}

function getStatusString($s)
{
    if ($s) {
        return "Hoạt động";
    } else {
        return "Hoạt động";
    }
}

function getStatusClass($s)
{
    if ($s) {
        return "badge bg-success";
    } else {
        return "badge bg-danger";
    }
}

function remove_boolean_array($arr)
{
    if (count($arr) > 0) {
        return array_filter($arr, function ($el) {
            return $el !== false;
        });
    }
    return $arr;
}

function checkDataError($data)
{
    if (isset($data['error'])) {
        return false;
    }
    return true;
}

function returnDataError($data)
{
    if (isset($data['error'])) {
        return ['error_' => $data['error']];
    }
    return 'Lỗi logic vui lòng liên hệ admin';
}

function random_array_value($array)
{
    if (!$array || empty($array)) {
        return false;
    }
    $k = array_rand($array);
    $v = $array[$k];
    return $v ? $v : false;
}

function cacheCron($key, $minutes = 1)
{
    if (\Illuminate\Support\Facades\Cache::has($key)) {
        return true;
    } else {
        \Illuminate\Support\Facades\Cache::remember($key, 60 * $minutes, function () {
            return true;
        });
    }
    return false;
}

function getDomainAgency()
{
    return env('DOMAIN_API_AGENCY', 'https://fb-api.online');
}

function getDomainCTVSUBVN()
{
    return env('DOMAIN_CTVSUBVN', 'https://autofb.pro');
}

function getDomainVNFB()
{
    return env('DOMAIN_VNFB', 'https://vietnamfb.com');
}

function getAutoCC()
{
    return env('DOMAIN_AUTOCC', 'https://us-central1-autolike-cc-f0644.cloudfunctions.net');
}

function getUri($only = false)
{
    if ($only && strpos($_SERVER['REQUEST_URI'], "?") > -1) {
        return strstr($_SERVER['REQUEST_URI'], "?", true);
    }
    return $_SERVER['REQUEST_URI'];
}

function getUriDiary()
{
    return '#';
}

function getUriOrder()
{

    $uri = $_SERVER['REQUEST_URI'];
    return strstr($uri, '/nhat-ky', true);
}

function getCurrency()
{
    $domain = 'http://' . $_SERVER['HTTP_HOST'];
    $keyCacheSite = build_key_cache(['keyCacheSite', $domain]);
    $site_config = \Illuminate\Support\Facades\Cache::get($keyCacheSite);
    if (isset($site_config->currency)) {
        return $site_config->currency;
    }
    return "vnđ";
}

function strip_unicode($str)
{
    if (!$str) return false;
    $unicode = array(
        'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
        'd' => 'đ',
        'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i' => 'í|ì|ỉ|ĩ|ị',
        'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
    );
    foreach ($unicode as $nonUnicode => $uni) $str = preg_replace("/($uni)/i", $nonUnicode, $str);
    return $str;
}

function build_key_cache($arr)
{
    $key = '';
    if ($arr) {
        foreach ($arr as $index => $a) {
            if ($index == 0) {
                $key .= $a;
            } else {
                $key .= '_' . $a;
            }
        }
    }
    return $key;
}

function check_ip_log_request()
{
    try {
        $ip = request()->ip();
        if (in_array($ip, ['27.78.235.81'])) {
            return true;
        }
    } catch (Exception $exception) {
        return false;
    }
    return false;
}

function forget_cache_get_tool($site_id)
{
    try {

        $list_level = \App\Models\Level::where('site_id', $site_id)->pluck('id')->toArray();
        foreach ($list_level as $item) {
            \Illuminate\Support\Facades\Cache::forget(build_key_cache(['get_tool', $site_id, $item]));
        }
    } catch (Exception $exception) {
    }
    return false;
}

function call_api($post_data, $url)
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

function call_socket($post_data, $url)
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
        CURLOPT_POSTFIELDS => json_encode($post_data),
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);

}

function string2Stars($string = '', $first = 0, $last = 0, $rep = '*')
{
    $begin = substr($string, 0, $first);
    $middle = str_repeat($rep, strlen(substr($string, $first, $last)));
    $end = substr($string, $last);
    $stars = $begin . $middle . $end;
    return $stars;
}

function echo_now($str)
{
    echo "-------------" . $str . "-------" . date("d-m-Y H:i:s") . "\n";
}

function delay_time($time)
{
    for ($i = 0; $i < $time; $i++) {
        echo_now($i + 1);
        sleep(1);
    }
}

function getTool($site_id, $token)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://fb-api.online/api/tools?site_id=" . $site_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Connection: keep-alive",
            "Pragma: no-cache",
            "Cache-Control: no-cache",
            "Accept: application/json, text/plain, */*",
            "Authorization: Bearer " . $token,
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.105 Safari/537.36",
            "Origin: http://hackfb.info",
            "Sec-Fetch-Site: cross-site",
            "Sec-Fetch-Mode: cors",
            "Sec-Fetch-Dest: empty",
            "Referer: http://hackfb.info/site-config",
            "Accept-Language: en-US,en;q=0.9"
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
}

function redirectLogin()
{
    return redirect('/dang-nhap')->with(['error_' => 'Đăng nhập hết hạn vui lòng đăng nhập lại']);
}

function callApiToAgencyWithToken($url, $data = [], $token, $method = 'POST', $timeout = 30000, $debug = false)
{
    if (in_array('_token', $data)) {
        unset($data['_token']);
    }
    $curl = curl_init();

    // Optional Authentication:
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
            if ($data) {
                if (!isset($data['limit'])) {
                    $data['limit'] = env("PAGINATE", 99);
                }
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
    }
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "username:password");
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . trim($token),
    ));

    $result = curl_exec($curl);

    if (curl_errno($curl) != 0) {
        return false;
    }

    if ($debug) {
        try {
            sendMessOrderFailToBotTelegram($result);
        } catch (Exception $exception) {
        }
    }

    if ($result === FALSE) {
        return false;
    }

    curl_close($curl);

    return json_decode($result);
}

function addSeverName()
{
    return env("JS_DOMAIN_ON", false) ? str_replace(".", "", $_SERVER['SERVER_NAME']) : '';
}

function sendMessOrderFailToBotTelegram($mess)
{
    $curl = curl('https://api.telegram.org/bot1194217314:AAHumFdWcsEqYxogwgcVlw2HDQH1C_SyYn0/sendMessage?chat_id=983738766&text=' . urlencode($mess));
    return $curl;
}

function curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_FAILONERROR, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function curlApi($url, $data = [], $method = 'POST')
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://tangseeding.net/api/payment/callback',
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
    sendMessOrderFailToBotTelegram($response);
    curl_close($curl);
    return json_decode($response);
}

function appendSpan($class, $str)
{
    if ($class > 4) {
        $class = 0;
    }
    $array = [
        'info',
        'danger',
        'warning',
        'success',
    ];
    return '<span class="badge badge-pill badge-' . $array[$class - 1] . '">' . $str . '</span>';
}

function cutString($str, $stack)
{
    if (strlen($str) > $stack) {
        return substr($str, 0, $stack) . '...';
    }
}

function getStatusMessage($item)
{
    if ($item->is_removed == 1) {
        if ($item->count_refund > 0) {
            return appendHtml(4, $item->status_message);
        }
        return appendHtml(3, $item->status_message);
    }
    if ($item->is_refund == 1 && $item->count_refund == 0) {
        return appendHtml(4, $item->status_message);
    }
    if ($item->is_refund == 1 && $item->count_refund > 0) {
        return appendHtml(4, $item->status_message);
    }
    if ($item->is_hidden == 1) {
        return appendHtml(3, $item->status_message);
    }
    if ($item->object_not_exist == 1) {
        if ($item->status == 2) {
            return appendHtml(3, $item->status_message);
        } else {
            if ($item->type == 'like_page' && $item->count_is_run < 1) {
                return appendHtml(3, $item->status_message);
            }
            return appendHtml(3, $item->status_message);
        }
    }
    if ($item->status == 2) {
        if ($item->quantity > $item->count_is_run) {

            return appendHtml(3, $item->status_message);
        }
        if ($item->quantity > $item->count_success && $item->is_warranty == 1) {
            return appendHtml(3, $item->status_message);
        }
        return appendHtml(4, $item->status_message);
    } elseif ($item->status == 1) {
        if ($item->time_expired > date('Y-m-d H:i:s')) {
            if ($item->type == 'view') {
                return appendHtml(2, $item->status_message);
            } else {
                return appendHtml(1, $item->status_message);
            }
        } else {
            return appendHtml(2, $item->status_message);
        }
    } elseif ($item->status == 0) {
        if ($item->time_expired > date('Y-m-d H:i:s')) {
            if ($item->type == 'view') {
                return appendHtml(1, $item->status_message);
            } else {
                return appendHtml(1, $item->status_message);
            }
        } else {
            return appendHtml(3, $item->status_message);
        }
    }
    return appendHtml(1, $item->status_message);
}

function appendHtml($class, $text)
{
    $array = [
        'info',
        'danger',
        'warning',
        'success',
    ];
    return '<span class="badge badge-pill badge-' . $array[$class - 1] . '">' . $text . '</span>';
}

function redirectBack($data)
{
    return redirect()->back()->with($data);
}

function redirectBackError($data)
{
    return redirect()->back()->with(['error' => $data]);
}

function redirectBackError_($mes)
{
    return redirect()->back()->with(['error_' => $mes]);
}

function redirectBackError__($mes)
{
    return redirect()->back()->with(['error__' => $mes]);
}

function redirectBackSuccess($mes)
{
    return redirect()->back()->with(['success' => $mes]);
}

function redirectUrl($url, $status = false, $data = false)
{
    return redirect($url)->with([$status => $data]);
}

function convertDatedmYHis($date)
{
    return date('d-m-Y H:i:s', strtotime($date));
}

function getDomainMfb()
{
    return env('DOMAIN_MFB', 'https://api.mfb.vn');
}

function getDomainTLC()
{
    return env('DOMAIN_MRDARK', 'https://tanglikecheo.com');
}

function str_rand($length)
{
    return substr(hash('sha256', mt_rand()), 0, $length);
}

function getLevel($level = 1)
{
    switch ($level) {
        case 1:
            return 'Khách hàng';
            break;
        case 2:
            return 'Đại lý';
            break;
        case 3:
            return 'Nhà phân phối';
            break;
        case 4:
            return 'Nhà phân phối cấp 1';
            break;
        default:
            return 'Khách hàng';
            break;
    }
}

function getMe()
{
    if (request()->user) {
        return request()->user;
    } else {
        return \Illuminate\Support\Facades\Auth::user();
    }
    return \Illuminate\Support\Facades\Auth::user();
}

function isAdmin()
{
    $me = getMe();
    if ($me->role == 'admin') {
        return true;
    }
    return false;
}

function getInfoUser($type)
{
    $user = getMe();
    switch ($type) {
        case 'img':
            return $user->avatar ?? DEFAULT_IMG;
            break;
        case 'name':
            return $user->name ?? $user->username;
            break;
        case 'email':
            return $user->email ?? 'Chưa cài đặt';
            break;
        case 'coin':
            return number_format($user->coin) . " " . DON_VI ?? 'Đang cập nhật';
            break;
        case 'username':
            return $user->username;
            break;
        case 'phone_number':
            return $user->phone_number ?? '0xxxxxxxxx';
            break;
        case 'level':
            return $user->level_user->name ?? 'Khách hàng';
            break;
        case 'level_id':
            return $user->level;
            break;
        case 'total_month':
            $total = PaymentMonth::where('year', date('Y'))->where('month', date('m'))->where('user_id', Auth::user()->id)->first();
            return number_format($total->coin ?? 0) . "đ";
            break;
        default:
            return $user->$type;
            break;
    }
}

function getDonVi()
{
    return DON_VI;
}

function getLogo()
{
    return '/assets/images/logo-cut.jpg';
}

function getTitle()
{
    return TITLE;
}

function getCategory()
{
    $key = 'category_v2_' . \Illuminate\Support\Facades\Auth::user()->role;
    if (\Illuminate\Support\Facades\Cache::has($key)) {
        return \Illuminate\Support\Facades\Cache::get($key);
    } else {
        return \Illuminate\Support\Facades\Cache::rememberForever($key, function () {
            return \App\Models\CategoryLabel::where('status', 1)->where(function ($q) {
                if (\Illuminate\Support\Facades\Auth::user()->role != 'admin') {
                    $q->where('id', '<>', 2);
                }
            })->with('category', 'category.menu')->orderBy('sort', 'ASC')->get();
        });
    }
}

function addLogsHandleCoin($coin, $user)
{
    \Illuminate\Support\Facades\DB::table('logs_handle_coin')->insert(
        [
            'created_at' => date('Y-m-d H:i:s'),
            'coin' => $coin,
            'old_coin' => $user->coin,
            'new_coin' => $user->coin - $coin,
            'user_id' => $user->id,
            'post_data' => json_encode($_POST)
        ]
    );
}

function dataToText($data)
{
    $txt = '';
    foreach ($data as $i => $item) {
        $txt = $txt . " $i : $item \n";
    }
    return $txt;
}

function sendToTelegramId($mess, $id)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.telegram.org/bot5417742397:AAF3-0UvL5Rwqad7gh8liYFJPpsCIgKaHdc/sendMessage?chat_id=' . $id . '&text=' . urlencode($mess),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 1,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return true;
}

function checkIsApi()
{
    if (request()->is('api*')) {
        return true;
    } else {
        return false;
    }
}

function returnResponseError($result)
{
    $res = [
        'success' => false,
        'hold_coin' => $result['hold_coin'] ?? false,
        'message' => $result['message'] ?? 'Thất bại',
        'status' => 400,
        'data' => []
    ];
    if (isset($result['error']) && is_array($result['error']) && count($result['error']) > 0 || $result['status'] == 422) {
        $data = $result['error']->toArray();
        $message = 'Dữ gửi lên không hợp lệ';
        foreach ($data as $i => $item) {
            if (isset($item[0])) {
                $message = $item[0];
            }
        }
        $res['status'] = 422;
        $res['message'] = $message;
        $res['error'] = $result['error'];
        return response()->json($res, $res['status']);
    }
    if (isset($result['error___'])) {
        $res['status'] = 422;
        $res['error___'] = $result['error___'];
        return response()->json($res, $res['status']);
    }
    if (isset($result['error_'])) {
        $res['status'] = 400;
        $res['message'] = $result['error_'];
        return response()->json($res, $res['status']);
    }
    return response()->json($res, $res['status']);
}

function returnResponseSuccess($result)
{
    $res = [
        'success' => true,
        'data' => $result['data'] ?? [],
        'status' => 200,
        'mesasge' => $result['message'] ?? 'Thành công',
    ];
    return response()->json($res, $res['status']);
}

?>
