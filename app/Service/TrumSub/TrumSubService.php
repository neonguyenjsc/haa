<?php


namespace App\Service\TrumSub;


use App\Http\Controllers\Traits\Lib;
use App\Models\Config;
use App\Service\Telegram\TelegramService;
use PHPUnit\Exception;

class TrumSubService
{
    //https://trumsub.vn/
    use Lib;

    public function callApi($url, $data)
    {
        $proxy = $this->getProxy();
        try {
            $t = new TelegramService();
            $t->sendMessGroupCardToBotTelegram("trumsub1 => 123");
        } catch (\Exception $exception) {
            $t->sendMessGroupCardToBotTelegram("trumsub => ");
        }
        $url = sprintf("%s?%s", $url, http_build_query($data));
        $url = $url;
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
            CURLOPT_HTTPHEADER => array(
                'authority: trumsub.vn',
                'accept: application/json, text/javascript, */*; q=0.01',
                'x-requested-with: XMLHttpRequest',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'origin: https://trumsub.vn',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://trumsub.vn/subvip.html',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
            ),
//            CURLOPT_PROXY => $proxy->ip . ':' . $proxy->port,
//            CURLOPT_PROXYUSERPWD => $proxy->username . ':' . $proxy->password,
        ));
        $response = curl_exec($curl);
        try {
//            if (isAdmin()) {
//                dd($response);
//            }
            $t = new TelegramService();
            $t->sendMessGroupCardToBotTelegram("trumsub =>2 " . $response . " => " . json_encode($data));
        } catch (\Exception $exception) {
            $t->sendMessGroupCardToBotTelegram("trumsub => " . $exception->getMessage() . " => " . $exception->getLine());
        }
        curl_close($curl);
        return json_decode($response);
    }

    public function callGetList($url, $data)
    {

        $config_token = Config::where('alias', 'trum_like_vn')->first();
        $data_config_token = $config_token->value;
        $data['api'] = $data_config_token;
        $curl = curl_init();
//        $data = sprintf("", );
        //(http_build_query($data));
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Cookie: PHPSESSID=93a0bc50f29de29127604e11df18a240'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}
