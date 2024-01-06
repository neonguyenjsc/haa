<?php


namespace App\Service\TangLikeCheo;


use App\Http\Controllers\Traits\Lib;
use Illuminate\Support\Facades\DB;

class TangLikeCheoService
{
    use Lib;

    public function getPricesMaster($type, $provider = 'facebook')
    {
        $token = DB::table('config')->where('alias', 'key_tanglikecheo')->first();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_TANG_LIKE_CHEO . "/api/prices",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $token->value,
                't:' . time(),
                "Cookie: __cfduid=d0264a7349fb73556a43978a0306ec89e1598193728"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $data = json_decode($response);
        if ($provider == 'facebook') {
            if (isset($data->data->data->config->$type)) {
                return $data->data->data->config->$type;
            } else {
                return false;
            }
        } else {
            if (isset($data->data->data->config_instagram->$type)) {
                return $data->data->data->config_instagram->$type;
            } else {
                return false;
            }
        }
    }

    public function callApi($url, $post_data, $method = 'POST')
    {
        $token = DB::table('config')->where('alias', 'key_tanglikecheo')->first();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POSTREDIR, 3);
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($post_data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($post_data)
                    $url = sprintf("%s?%s", $url, http_build_query($post_data));
        }
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            't:' . time(),
            "Authorization: Bearer " . trim($token->value ?? '')
        ]);

        $result = curl_exec($curl);
        $this->sendMessGroupCardToBotTelegram("tlc " . $result);
        curl_close($curl);
        if (strpos($result, "<title>500") || strpos($result, "<title>502") || $result == '' || !$result) {
            return json_decode('{"status":400,"success":false,"message":"Tạo thất bại lỗi #1"}');
        }
        return json_decode($result);
    }

    public function callApiWithToken($url, $post_data, $method = 'POST')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POSTREDIR, 3);
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($post_data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($post_data)
                    $url = sprintf("%s?%s", $url, http_build_query($post_data));
        }
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            't:' . time(),
            "Authorization: Bearer eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiI2MDQ5NTQzODU5YmU2NjUzODg4NmRiZDMiLCJwYXNzd29yZCI6ImQxOTcwNGZmMjA2NTlkMWEwNDU0MGQ0ZWQ3NmVlZDgwIiwic3RhdHVzIjowLCJpYXQiOjE2NDcxNDcxNDksImV4cCI6NTI0NzE0MzU0OX0.gpiPGl8zGrp_e49oqWKlES4WSUCtZbLFDT3RKC9USfwergFzNCU039QSn-qwgq0hP9N3vMkqtvGDQZ9vI64RuA"
        ]);

        $result = curl_exec($curl);

        if ($result === FALSE) {
            return false;
        }
        curl_close($curl);

        return json_decode($result);
    }
}
