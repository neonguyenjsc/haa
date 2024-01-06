<?php


namespace App\Service\ThanhLike;


class ThanhLikeService
{
    public function buy($url, $data)
    {
        $dataPost = array(
            "token" => getConfig('key_thanh_like'), //token từ hệ thống
            "package" => $data['package'],
            "objectType" => $data['objectType'],
            "server" => $data['server'],
            "objectId" => $data['objectId'],
            "amount" => $data["amount"]
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($dataPost),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "accept: application/json")
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function login()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://thanhlike.net/ajax.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'username=bengucto&password=123123@#$%@&action=login',
            CURLOPT_HTTPHEADER => array(
                'authority: thanhlike.net',
                'accept: application/json, text/javascript, */*; q=0.01',
                'accept-language: en-US,en;q=0.9',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'cookie: PHPSESSID=0926c54a216485cf8c8c580df0e1dcc5; PHPSESSID=b54cfdbcbc936ba904096d2350fa09f4',
                'origin: https://thanhlike.net',
                'referer: https://thanhlike.net/auth/login',
                'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="102", "Microsoft Edge";v="102"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'sec-fetch-dest: empty',
                'sec-fetch-mode: cors',
                'sec-fetch-site: same-origin',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36 Edg/102.0.1245.39',
                'x-requested-with: XMLHttpRequest'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }
}
