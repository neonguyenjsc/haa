<?php


namespace App\Service\DichVuOnlineVnService;


use App\Http\Controllers\Traits\Lib;
use App\Models\Config;

class DichVuOnlineVnService
{
    use Lib;

    public function buy($data)
    {

        if (isset($data['user'])) {
            unset($data['user']);
        }
        $key = Config::where('alias', 'key_dichvuonlinevn')->first();
        $data['key'] = $key->value ?? '';
        $data['action'] = 'add';
        $data['link'] = $data['object_id'] ?? '';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_DVO . "/api/v2",
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
        $this->sendMessGroupCardToBotTelegram("dichvuonline\n " . $response);

        curl_close($curl);
        try {
            if (strpos($response, "<title>500") || $response == '' || !$response) {
                return json_decode('{"status":400,"success":false,"message":"Tạo thất bại lỗi #1"}');
            }
        } catch (\Exception $exception) {
        }
        return json_decode($response);
    }

    public function checkOrder($data)
    {
        if (isset($data['user'])) {
            unset($data['user']);
        }
        $key = Config::where('alias', 'key_dichvuonlinevn')->first();
        $data['key'] = $key->value ?? '';
        $data['action'] = 'status';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_DVO . "/api/v2",
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
        $this->sendMessGroupCardToBotTelegram("dichvuonline\n " . $response);

        curl_close($curl);
        try {
            if (strpos($response, "<title>500") || $response == '' || !$response) {
                return json_decode('{"status":400,"success":false,"message":"Tạo thất bại lỗi #1"}');
            }
        } catch (\Exception $exception) {
        }
        return json_decode($response);
    }
}
