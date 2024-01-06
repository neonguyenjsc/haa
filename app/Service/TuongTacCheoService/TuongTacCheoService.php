<?php


namespace App\Service\TuongTacCheoService;


use App\Http\Controllers\Traits\Lib;

class TuongTacCheoService
{
    use Lib;

    public function login()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://tuongtaccheo.com/login.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'username=bengucto&password=123123%40%23%24&submit=%C4%90%C4%82NG%2BNH%E1%BA%ACP',
            CURLOPT_HTTPHEADER => array(
                'authority: tuongtaccheo.com',
                'cache-control: max-age=0',
                'upgrade-insecure-requests: 1',
                'origin: https://tuongtaccheo.com',
                'content-type: application/x-www-form-urlencoded',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: navigate',
                'sec-fetch-user: ?1',
                'sec-fetch-dest: document',
                'referer: https://tuongtaccheo.com/index.php',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
                'cookie: _gid=GA1.2.886006062.1669650627; PHPSESSID=d3go1i49kr4d2q308jv6pfl2g5; _ga_6RNPVXD039=GS1.1.1669728573.2.1.1669728599.0.0.0; _ga=GA1.2.1000427561.1669396124'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    public function buy($url, $data)
    {
        $this->login();
        $data = http_build_query($data);
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
                'authority: tuongtaccheo.com',
                'accept: */*',
                'x-requested-with: XMLHttpRequest',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'origin: https://tuongtaccheo.com',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://tuongtaccheo.com/tiktok/tanglike/',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
                'cookie: _gid=GA1.2.886006062.1669650627; PHPSESSID=d3go1i49kr4d2q308jv6pfl2g5; _ga_6RNPVXD039=GS1.1.1669728573.2.1.1669728599.0.0.0; _ga=GA1.2.1000427561.1669396124'
            ),
        ));
        $response = curl_exec($curl);
        $this->sendMessGroupCardToBotTelegram("ttc " . $response);
        curl_close($curl);
        try {
            if (strpos($response, "<title>500") || $response == '' || !$response) {
                return "Tạo thất bại lỗi #1";
            }
        } catch (\Exception $exception) {
        }
        return $response;
    }


    public function checkOrder($link, $id, $page = 0)
    {
        $this->login();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $link,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'page=&id=' . $id,
            CURLOPT_HTTPHEADER => array(
                'authority: tuongtaccheo.com',
                'cache-control: max-age=0',
                'upgrade-insecure-requests: 1',
                'origin: https://tuongtaccheo.com',
                'content-type: application/x-www-form-urlencoded',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: navigate',
                'sec-fetch-user: ?1',
                'sec-fetch-dest: document',
                'referer: https://tuongtaccheo.com/index.php',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
                'cookie: _gid=GA1.2.886006062.1669650627; PHPSESSID=d3go1i49kr4d2q308jv6pfl2g5; _ga_6RNPVXD039=GS1.1.1669728573.2.1.1669728599.0.0.0; _ga=GA1.2.1000427561.1669396124'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);

    }
}
