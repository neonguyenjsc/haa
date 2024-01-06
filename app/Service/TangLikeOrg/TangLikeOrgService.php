<?php


namespace App\Service\TangLikeOrg;


use App\Http\Controllers\Traits\Lib;
use App\Models\Config;
use App\Service\Telegram\TelegramService;

class TangLikeOrgService
{
    use Lib;
    public function callApi($data)
    {
        $token = Config::where('alias', 'key_tanglikeorg')->first();
        $curl = curl_init();
        $id = $data['object_id'];
        $note = $data['notes']; // ghi chú có thể bỏ trống
        $soluong = $data['quantity'];
        $type = $data['type']; //package_name_master sub-tocdo-new-sv3
        $modun = $data['modun']; //package_name_master sub-tocdo-new-sv3
        $baiviet = $data['baiviet']; // package_name_master
        $tokensite = $token->value ?? '';

        curl_setopt_array($curl, array(
            CURLOPT_URL => TANG_LIKE_ORG . "/2T_modun/modun_post.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "t=buy-buff-fb&baiviet=$baiviet&id=$id&note=$note&soluong=$soluong&type=$type&modun=$modun&tokensite=$tokensite",
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
//                "Cookie: __cfduid=d6090d9cd8c52dd97651fa959ebfb3cd01597767144; PHPSESSID=207bd52eed51fb9e863bb4a0af6c90e1"
            ),
        ));
        $response = curl_exec($curl);
        $this->sendMessGroupCardToBotTelegram("tanglike.org " . $response);
        curl_close($curl);
        $decoded = json_decode($response);
        return $decoded;
    }

    public function buySub($data)
    {
        $token = Config::where('alias', 'key_tanglikeorg')->first();
        $curl = curl_init();
        $id = $data['object_id'];
        $note = $data['notes']; // ghi chú có thể bỏ trống
        $soluong = $data['quantity'];
        $type = $data['type'];
        $modun = $data['modun'];
        $baiviet = $data['baiviet'];
        $tokensite = $token->value ?? '';

        curl_setopt_array($curl, array(
            CURLOPT_URL => TANG_LIKE_ORG . "/2T_modun/modun_post.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "t=buy-buff-fb&baiviet=$baiviet&id=$id&note=$note&soluong=$soluong&type=$type&modun=$modun&tokensite=$tokensite&baohanh=7",
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
//                "Cookie: __cfduid=d6090d9cd8c52dd97651fa959ebfb3cd01597767144; PHPSESSID=207bd52eed51fb9e863bb4a0af6c90e1"
            ),
        ));

        $response = curl_exec($curl);
        try {
            $t = new TelegramService();
            $t->sendMessGroupCardToBotTelegram("tanglikeorg " . $response);
        } catch (\Exception $e) {
        }
        curl_close($curl);

        $decoded = json_decode($response);
        return $decoded;
    }
}
