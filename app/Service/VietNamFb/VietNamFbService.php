<?php


namespace App\Service\VietNamFb;


use App\Models\Config;

class VietNamFbService
{

    public function buy($data, $url)
    {
//        $this->login();
        $data = http_build_query($data);
        $response = $this->curlVnfb($url, $data, true);
        return json_decode($response);
    }

    public function login()
    {
        $key = Config::where('alias', 'key_vnfb')->first();
        $config = json_decode($key->value);
        $data = 'username=' . $config->username . '&password=' . $config->password;


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://vietnamfb.com/?mc=pub&site=postLogin',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'authority: vietnamfb.com',
                'cache-control: max-age=0',
                'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'upgrade-insecure-requests: 1',
                'origin: https://vietnamfb.com',
                'content-type: application/x-www-form-urlencoded',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36',
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: navigate',
                'sec-fetch-user: ?1',
                'sec-fetch-dest: document',
                'referer: https://vietnamfb.com/dang-nhap',
                'accept-language: en-US,en;q=0.9',
                'cookie: __cf_bm=pbiFPImRLp52cFw27GTh01TvpOcP9fln.tM69osjHzA-1636605083-0-AUHuI8TfEE0tpnNd5uECk6EGSdrKjRYeVTD6gGMDAmTxkJrrQei3OT7/wJRFJVO+KU64Zp7GfJem/1HsgpFtQBiTMOWfsp8OVdB/fM4EchMBJySaA3fG9W27CpeIMTUl6Q==; PHPSESSID=toeir74vp8aobo6es9mj5m4264; _ggwp=eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJodHRwczpcL1wvdmlldG5hbWZiLmNvbVwvIiwiaWF0IjoxNjM2NjA1MjgxLCJleHAiOjE2MzY2MjY4ODEsInVzZXJuYW1lIjoiZ2lhcHRoYW5ocXVvYyJ9.D99oGjFxRbEXo2td1pMsRnMCNZMIcpZXw4I91k4nvJkhvEXQL9CG7scSx9-uLTesNhYuJmpNoHVXS99pWsGSpMcX444oDLoXxHvAUhkaXvv_XaxcuGFVMj1Ko_gYLeMhk7BJVKEaej2FmCFTACsBSinRum3ATmKPtOBBXc65nZNw69Sh2QQus5BIrM3Wo0Wahdb1j2O21b8DnXqzJbezmLkSzOS7nNCXgp-wO-oP09BfWgzXq0mbgM1zYooDupDvVKgt5RFMCGWaY9XJcg0zoGDZ9TSC5VLXpYJ6up7FbLmyPPuejNyfS8QercI0kLVHNVSAY_zNHk9Nbi6XMzOXyA'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

    }

    public function curlVnfb($url, $data, $debug = false)
    {
        $config = Config::where('alias', 'key_vnfb')->first();
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'authority: vietnamfb.com',
                'cache-control: max-age=0',
                'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'upgrade-insecure-requests: 1',
                'origin: https://vietnamfb.com',
                'content-type: application/x-www-form-urlencoded',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36',
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: navigate',
                'sec-fetch-user: ?1',
                'sec-fetch-dest: document',
                'referer: https://vietnamfb.com/dang-nhap',
                'accept-language: en-US,en;q=0.9',
                'cookie: ' . $config->value
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}
