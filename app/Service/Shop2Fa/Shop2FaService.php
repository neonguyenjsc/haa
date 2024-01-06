<?php


namespace App\Service\Shop2Fa;


use App\Http\Controllers\Traits\Lib;

class Shop2FaService
{
    use Lib;

    public function callApi($data, $url)
    {
        $this->login2Fa();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Connection: keep-alive',
                'Accept: application/json, text/plain, */*',
                'TK: ' . getConfig('key_2fa'),
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36',
                'Content-Type: application/json;charset=UTF-8',
                'Origin: http://2fa.shop',
                'Referer: http://2fa.shop/',
                'Accept-Language: en-US,en;q=0.9',
                'Cookie: ' . getConfig('key_2fa')
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $this->sendMessGroupCardToBotTelegram("2fa" . $response);
        return json_decode($response);
    }

    public function login2Fa()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://2fa.shop/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "username": "huynhquocdai",
    "password": "123123@td"
}',
            CURLOPT_HTTPHEADER => array(
                'Connection: keep-alive',
                'Accept: application/json, text/plain, */*',
                'TK: ' . getConfig('key_2fa'),
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36',
                'Content-Type: application/json;charset=UTF-8',
                'Origin: http://2fa.shop',
                'Referer: http://2fa.shop/',
                'Accept-Language: en-US,en;q=0.9',
                'Cookie: ' . getConfig('key_2fa')
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}
