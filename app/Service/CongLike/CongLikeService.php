<?php


namespace App\Service\CongLike;


use App\Models\Config;

class CongLikeService
{
    public function callApi($data,$url)
    {
        $curl = curl_init();
        $config_token = Config::where('alias', 'cong_like_token')->first();
        $data_config_token = $config_token->value;
        $data['token'] = $data_config_token;
        $url = sprintf("%s?%s", $url, http_build_query($data));
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
                'Cookie: PHPSESSID=0bc7a0fe93815835b714e8fc99568fa2'
            ),
        ));
        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}
