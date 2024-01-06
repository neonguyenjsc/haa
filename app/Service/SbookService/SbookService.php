<?php


namespace App\Service\SbookService;


class SbookService
{

    public function callApi($data)
    {
        $url = DOMAIN_SBOOKS;
        $url = sprintf("%s?%s", $url, http_build_query($data));
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: __cfduid=d6225a0008af9b8c788e8762f943753421608041428; PHPSESSID=24e6e6edcb4e01e5d25c6d0609806012'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function callApiLikePage($data)
    {
        //https://sbooks.me/api/add_likepage/?uid=1000xxxxx&soluong=1000&user=19HKyNLcz8xxxxx&access_token=123
        $url = 'https://sbooks.me/api/add_likepage';
        $url = sprintf("%s?%s", $url, http_build_query($data));
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: __cfduid=d6225a0008af9b8c788e8762f943753421608041428; PHPSESSID=24e6e6edcb4e01e5d25c6d0609806012'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}
