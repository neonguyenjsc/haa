<?php


namespace App\Service\AutoLikeCC;


use App\Http\Controllers\Traits\Lib;
use App\Models\Config;

class AutoLikeCCService
{
    use Lib;

    public function callAutoCC($data, $url)
    {
        $config_token = Config::where('alias', 'key_autolike_v2')->first();
        $data_config_token = json_decode($config_token->value);
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
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'authority: api-autolike.congaubeo.us',
                'accept: application/json',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
                'token: ' . $data_config_token->token ?? '',
                'agency-secret-key: ' . $data_config_token->agency_secret_key ?? '',
                'content-type: application/json',
                'origin: https://www.mottrieu.com',
                'sec-fetch-site: cross-site',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://www.mottrieu.com/',
                'accept-language: vi',
                'Cookie: __cfduid=d6f82b0ecbbd5fcdc3d82712dfa53082e1609122016'
            ),
        ));

        $response = curl_exec($curl);
//        try {
//            $this->sendMessGroupCardToBotTelegram($response);
//        } catch (\Exception $exception) {
//        }
        curl_close($curl);
        return json_decode($response);
    }

    public function createComment($data)
    {
        $config_token = Config::where('alias', 'key_autolike_v2')->first();
        $data_config_token = json_decode($config_token->value);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://agency.autolike.cc/public-api/v1/agency/comments/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'token: ' . $data_config_token->token ?? '',
                'agency-secret-key: ' . $data_config_token->agency_secret_key ?? '',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
}
