<?php


namespace App\Service\MuaViewVnService;


use App\Models\Config;

class MuaViewVnService
{
    public function buy($data)
    {
        if (isset($data['user'])) {
            unset($data['user']);
        }
        $key = Config::where('alias', 'key_muaviewvn')->first();
        $data['key'] = $key->value ?? '';
        $data['action'] = 'add';
        $data['link'] = $data['object_id'] ?? '';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://muaview.vn/api/v2',
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
                'Cookie: PHPSESSID=7301d7018373ab3bb0b64f6a3d2a4a8e'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function checkOrder($data)
    {
        if (isset($data['user'])) {
            unset($data['user']);
        }
        $key = Config::where('alias', 'key_muaviewvn')->first();
        $data['key'] = $key->value ?? '';
        $data['action'] = 'status';
        $data['link'] = $data['object_id'] ?? '';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://muaview.vn/api/v2',
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
                'Cookie: PHPSESSID=7301d7018373ab3bb0b64f6a3d2a4a8e'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}
