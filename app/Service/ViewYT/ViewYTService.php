<?php


namespace App\Service\ViewYT;


use App\Models\Config;

class ViewYTService
{

    public function callApi($data)
    {
        if (isset($data['user'])) {
            unset($data['user']);
        }
        $key = Config::where('alias', 'key_viewyt')->first();
        $data['key'] = $key->value ?? '';
        $data['action'] = 'add';
        $data['link'] = $data['object_id'] ?? '';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_VIEW_YT . "/api/v2",
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
//        $this->sendMessOrderFailToBotTelegram("Baostar\n " . $response);

        curl_close($curl);
        $response = json_decode($response);
        if ($response && isset($response->error) && $response->error == 'Not enough funds on balance') {
            $response->error = "Lỗi vui lòng liên hệ admin #0";
        }
        if ($response && isset($response->error) && $response->error == 'Not enough funds') {
            $response->error = "Lỗi vui lòng liên hệ admin #0";
        }
        return $response;
    }

    public function checkOrder($data)
    {
        if (isset($data['user'])) {
            unset($data['user']);
        }
        $key = Config::where('alias', 'key_viewyt')->first();
        $data['key'] = $key->value ?? '';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_VIEW_YT . "/api/v2",
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
        $response = json_decode($response);
        if ($response && isset($response->error) && $response->error == 'Not enough funds on balance') {
            $response->error = "Lỗi vui lòng liên hệ admin #0";
        }
        return $response;
    }
}
