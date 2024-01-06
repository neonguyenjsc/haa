<?php


namespace App\Service\SubReVn;


use App\Http\Controllers\Traits\Lib;

class SubReVnService
{
    use Lib;

    public function callApiSubGiaRe($url, $data)
    {
        $proxy = $this->getProxy();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60 * 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'authority: subgiare.vn',
                'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="96", "Google Chrome";v="96"',
                'x-csrf-token: I8VOsGgDahtjrWLh5RzMnplcDf86ut7Mqrs0tIYQ',
                'sec-ch-ua-mobile: ?0',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.93 Safari/537.36',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'accept: application/json, text/javascript, */*; q=0.01',
                'api-token: ' . getConfig('sub_gia_re_vn'),
                'x-requested-with: XMLHttpRequest',
                'sec-ch-ua-platform: "Windows"',
                'origin: https://subgiare.vn',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://subgiare.vn/service/facebook/sub-speed/order',
                'accept-language: en-US,en;q=0.9'
            ),
        ));

        $response = curl_exec($curl);
        $this->sendMessGroupCardToBotTelegram("zz => " . $response . " \n " . json_encode($data));
        curl_close($curl);

        return json_decode($response);
    }

    public function login()
    {

    }
}
