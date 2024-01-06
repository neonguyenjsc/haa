<?php


namespace App\Service\BuffViewer;


use App\Http\Controllers\Traits\Lib;
use App\Models\Config;

class BuffViewerService
{

    use Lib;

    public function callApi($data, $url)
    {
//        $data[0]['discount_code'] = "SEAGAMES ";
//        if ($url == 'https://buffviewer.com/api/orderviewvideounit/add' && in_array($data[0]['type'], ['61', '17', '60'])) {
//            $data[0]['discount_code'] = "MIN10_FEB_5485";
//        }
//        if ($url == 'https://buffviewer.com/api/orderviewvideounit/add' && in_array($data[0]['type'], ['62'])) {
//            $data[0]['discount_code'] = "MIN10_FEB_5485";
//        }
        $config = Config::where('alias', 'api_viewer')->first();
        if ($config) {
            $curl = curl_init();

            $header = array(
                "authorization:" . $config->value,
                "language:vi",
                "content-type:application/json"
            );


            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 180,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => $header,
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            $this->sendMessGroupCardToBotTelegram("buffviewer " . $response);
            curl_close($curl);

            if ($err) {
                if ($err) {
                    return json_decode('{"status":400,"success":false,"message":"Tạo thất bại lỗi #1"}');
                }
            } else {
                try {
                    if (strpos($response, "<title>500") || $response == '' || !$response) {
                        return json_decode('{"status":400,"success":false,"message":"Tạo thất bại lỗi #1"}');
                    }
                } catch (\Exception $exception) {
                }
                return json_decode($response);
            }
        } else {
            return false;
        }
    }

    public function getSL()
    {
        $key = Config::where('alias', 'api_viewer')->first();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://buffviewer.com/api/order/getavailableamount",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: " . $key->value,
                "Cookie: __cfduid=d61048cf504a74348ce85838c1ad95cc41601520751; XSRF-TOKEN=eyJpdiI6IkFaRkhNQkd1TGU4YVFvTjJaR0hBWnc9PSIsInZhbHVlIjoiK1JSN1dIS1VFOSthMkQ5WmNPRnprOFJpMTkxSVpjY1FuVU91ZlYrS2E2RFdPNyt6T0pjUE01ZkN0VG83ZFBMWSIsIm1hYyI6IjdjNWEwNWQ5OWZiYTMwYzBiNTA5OWNkNjg0MjgwYjcxYTQ3ZWJmYjVhZjFjMGZhNzgwNmYzZGIwNmE0NDNmMzYifQ%3D%3D; buff_viewer_session=eyJpdiI6ImVkOVdjclRhWlBUbWdySVE3c29xclE9PSIsInZhbHVlIjoicThiU0U2aDFwWHoyOGEzUk9NWTJQNjZNb1V3SDJPSjhhVk9rd1pJb29WMjJcL0poQUFhUklnMzB0VDd3ZU8wN3YiLCJtYWMiOiIxYjI2YzBkMTI1ZjUxYzQ5YWI4MjNmMWE0ZjNlOGI3MTc2YmUyOGJiZmM4Y2Y1MmNkOGNlMWQ3ZDViNjQ5ODNmIn0%3D"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}
