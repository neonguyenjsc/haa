<?php


namespace App\Service\TraoDoiSub;


use App\Service\Telegram\TelegramService;
use PHPUnit\Exception;

class TraoDoiSubService
{
    public $cookie = "cf_clearance=BHM.e1H_BIeoaA2fuxaJ3wiccBirrycO0pB2WFyTjII-1668406872-0-150; SL_G_WPT_TO=en; SL_GWPT_Show_Hide_tmp=1; SL_wptGlobTipTmp=1; PHPSESSID=f98bd2cd5d01b5f2d606bf93c71715cf";

    public function login()
    {
//        $config = getConfig('key_traodoisub');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_TRAODOISUB . '/scr/login.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'username=bengucto&password=123123!@#',
            CURLOPT_HTTPHEADER => array(
                'authority: traodoisub.com',
                'accept: application/json, text/javascript, */*; q=0.01',
                'x-requested-with: XMLHttpRequest',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'origin: https://traodoisub.com',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://traodoisub.com/',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
                'cookie: ' . $this->cookie
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    public function buy($url, $data)
    {
        $this->login();
        $data = http_build_query($data);
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
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'authority: traodoisub.com',
                'accept: */*',
                'x-requested-with: XMLHttpRequest',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'origin: https://traodoisub.com',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://traodoisub.com/mua/viewstr/',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
                'cookie: PHPSESSID=f98bd2cd5d01b5f2d606bf93c71715cf'
            ),
        ));

        $response = curl_exec($curl);
        try {
            $t = new TelegramService();
            $t->sendMessGroupCardToBotTelegram("tds => " . $response);
        } catch (Exception $exception) {
        }
        try {
            if (strpos($response, "<title>500") || $response == '' || !$response) {
                return "Tạo thất bại lỗi #1";
            }
        } catch (\Exception $exception) {
        }
        curl_close($curl);
        return $response;
    }
}
