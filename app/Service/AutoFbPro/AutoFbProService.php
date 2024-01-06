<?php


namespace App\Service\AutoFbPro;


use Illuminate\Support\Facades\DB;

class AutoFbProService
{
    public function callApi($data, $link)
    {
        $config = DB::table('config')->where('alias', 'key_ctvsubvn')->first();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $link,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "ht-token: " . $config->value ?? '',
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function getPrices($key)
    {
        $config = DB::table('config')->where('alias', 'key_ctvsubvn')->first();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://autofb.pro/api/admin/get_all_price_function/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "ht-token: " . $config->value ?? '',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //
        $response = json_decode($response);
        if (isset($response->data) && count($response->data) > 0) {
            foreach ($response->data as $item) {
                if ($item->name_table == $key) {
                    return $item->prices_web ?? false;
                }
            }
            return false;
        }
        return false;
    }
}
