<?php


namespace App\Service\DichVuSt;


use App\Http\Controllers\Traits\Lib;
use App\Models\Config;

class DichVuStService
{
    use Lib;

    public function buy($data)
    {
        if (isset($data['user'])) {
            unset($data['user']);
        }
        $key = Config::where('alias', 'key_dichvuonst')->first();
        $data['key'] = $key->value ?? '';
        $data['action'] = 'add';
        $data['link'] = $data['object_id'] ?? '';
        try {
            if ($_SERVER['PATH_INFO'] == '/api/tiktok-live/buy') {
                $data['link'] = getUrlReplaceString($data['link']) . '?enter_from_merge=others_homepage&enter_method=others_photo';
            }
        } catch (\Exception $exception) {
        }
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_DVST . '/api/v2',
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
                'Cookie: PHPSESSID=fsfuih63tpjlj4hdv5r6hdd5fd'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $this->sendMessGroupCardToBotTelegram("dichvuonst\n " . $response);
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
        $key = Config::where('alias', 'key_dichvuonst')->first();
        $data['key'] = $key->value ?? '';
        $data['action'] = 'status';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_DVST . '/api/v2',
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
                'Cookie: PHPSESSID=fsfuih63tpjlj4hdv5r6hdd5fd'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $this->sendMessGroupCardToBotTelegram("dichvuonst\n " . $response);
        return json_decode($response);

    }
}
