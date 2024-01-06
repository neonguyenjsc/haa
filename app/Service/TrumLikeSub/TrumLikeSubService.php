<?php


namespace App\Service\TrumLikeSub;


class TrumLikeSubService
{
    public function callApi($data, $url)
    {
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
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'authority: trumlikesub.vn',
                'accept: application/json, text/javascript, */*; q=0.01',
                'x-requested-with: XMLHttpRequest',
                'api-key: ' . getConfig('trum_like_sub'),
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'origin: https://trumlikesub.vn',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://trumlikesub.vn/service/facebook/vip-like/order',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function callApiV2($data, $url)
    {

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
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'authority: trumlikesub.vn',
                'accept: application/json, text/javascript, */*; q=0.01',
                'x-requested-with: XMLHttpRequest',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'origin: https://trumlikesub.vn',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://trumlikesub.vn/service/facebook/vip-like-clc-new',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
                'cookie: trumlikesubvn_session=eyJpdiI6ImJDa2kyZTR3MGwyOEswK3VoTmZTVXc9PSIsInZhbHVlIjoiYkxoS2poV0NrVXFrY1pmWmZtUmo3T2VlMmRpOU40Q0MwbEhUYkttWDVxcTE2ekhVbVRWMTBIWVZBSWhxS3ZhMHR5K2laUHRveEw0UjczYUErWUx2NlJaZDkvMFFnR3BiaVVhbUxXdjN0TkhNeldJcms3bDV6NFFPY0hnaytWeFEiLCJtYWMiOiI5NjRhYWJiZDA5MDQ1Nzc4MTYxZWJiMzJlZjIwOWNhYTkzMjhkNTdlNWI3ZTNiYTE4MGM3MGQxMWFhZTlhM2M1IiwidGFnIjoiIn0%3D; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6ImdEVFhwOWJWSjhwMjhQR05hZVRsdnc9PSIsInZhbHVlIjoid3dYN0JGYzBsL2pUUzZ5TUxXYnphdDg3bVhUZEY4MjU2RzNYYnR6dkNXUjNwUWdIS0dzSjNRbnpSakdsWFdXaDR0K1RNMFFkVjMzYWhaekxrT3pPemtlTlZENUs2eFBjdFNScGYyZWtielVoM0xGSWQvWnNZcktNU1FJajZuVWFxam1iM1ZJUTBnN0JRaSs1dUxUSkdTSHluSFRsaWFIbmtPODlsSkVGVlh4VU5NQ1hVK1pGbmlOZHdGK2dMM3M3QzVnaUlVTy85MGFOeXVLeTY0TTFZNkFpeE1DMGVmeWVHb0wvdmxtWGJvOD0iLCJtYWMiOiI5Y2VhOGZiYTZlY2RmMWY3ODNlODlhYzY2YmNhMDk1YTJmNWM3NjIxMmM2Yjg4YjhkMjc5M2M0NDNlN2Q5YjVjIiwidGFnIjoiIn0%3D; notify=true; _1Modal=true; SL_G_WPT_TO=en; SL_GWPT_Show_Hide_tmp=1; SL_wptGlobTipTmp=1; XSRF-TOKEN=eyJpdiI6IlRqUGVZNSsvbDlrT3p0NnRFRTVwV0E9PSIsInZhbHVlIjoic0VGUmY3SmdzNEZPV3Fwd0pNVUFqK0dvSEhPRkJUQ2xXc1ppd3FJemtUOVdpaS8vWUd5THhNM1Y1RmZGT1ZHcVVFVXB6Y3JBNUEyeUxmczQ5d0NEeHpnSUUwWnZPTzlWQ1JoN1JsZXlCYS9wckEybWdnN3Z5VkNEYXhIeE1mZEgiLCJtYWMiOiJlMDUyYmIzY2U5NzlkNmVlZTEwNjE1Mjk1NGZmNjQwZTlhZWJlMzg5NDJjYzRjMTdiODE5MThmMjRmMzhjNjc4IiwidGFnIjoiIn0%3D; trumlikesub_session=eyJpdiI6InpwRnRtMmp0a1U1VWFNdHZpdXFSWmc9PSIsInZhbHVlIjoiUnoxOWhIVVhWcE5MejI5L2ZkK1IrbW5ZOGNvdlVFUURmdDhzdnJpVDJsWXJ0T1dyOGFFcTZJUHdiRStVaTBGWEJoaDNtK1JZa3lVSEJhcEFOUm91L2VraWFaeW5HWFJFQ0ZURUZzb1gwSmxyTzBtOEl0d2F6MU9nKzRyY2NYY2EiLCJtYWMiOiI5NmI1ZjkxMGY2MzI1Yjg0YTA1NDU4YzJmNDhkNDdmOTQ0MjJlNWE0MzU2YmM4MjNlYzI5M2IwYjRkYzZkZjhmIiwidGFnIjoiIn0%3D'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }
}
