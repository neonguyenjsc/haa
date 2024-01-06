<?php


namespace App\Service\MFB;


use App\Service\Telegram\TelegramService;
use Illuminate\Support\Facades\DB;

class MfbService
{

    public function callApicallMfb($data, $url, $method = 'POST', $timeout = 18000)
    {
        $config = DB::table('config')->where('alias', 'key_mfb')->first();

        $curl = curl_init();

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . trim($config->value ? $config->value : ''),
        ));

        curl_setopt($curl, CURLOPT_POSTREDIR, 3);


        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        try {
            $t = new TelegramService();
            $t->sendMessGroupCardToBotTelegram("mfb => " . $result);
        } catch (\Exception $exception) {
        }
        if ($result === FALSE) {
            return false;
        }

        curl_close($curl);

        return json_decode($result);
    }

    public function getPricesMaster($tool_id, $package_name)
    {
        $config = DB::table('config')->where('alias', 'key_mfb')->first();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => getDomainMfb() . "/api/tools/price?tool_id=$tool_id&package_name=$package_name",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $config->value ?? '',

            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        return $response->data ?? false;

    }
}
