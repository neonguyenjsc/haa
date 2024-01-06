<?php


namespace App\Service\VnKings;


use App\Models\Config;

class VnKingService
{
    public function callApi($data)
    {
        $key = Config::where('alias', 'key_vnkings')->first();
        $data['key'] = $key->value ?? '';
        $data['action'] = 'add';
        $data['link'] = $data['object_id'] ?? '';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_VNKINGS . "/api/v2",
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
        return json_decode($response);
    }
}
