<?php


namespace App\Service\MlikeV2Service;


use App\Service\Telegram\TelegramService;

class MlikeV2Service
{
    public function callApi($url, $data)
    {
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
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'Cookie: PHPSESSID=2834kasdf9Asdfgf'
            ),
        ));

        $response = curl_exec($curl);
        try {
            $t = new TelegramService();
            $t->sendMessGroupCardToBotTelegram("mlike v2=>" . $response);
        } catch (\Exception$exception) {
        }
        curl_close($curl);
        return json_decode($response);
    }
}
