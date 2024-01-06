<?php


namespace App\Service\TrumVn;


use App\Http\Controllers\Traits\Lib;
use App\Service\Telegram\TelegramService;

class TrumVnService
{
    use Lib;

    public function addOrder($data)
    {
        //service_id=1&object_id=4&quantity=200&notes=&key=ea851000346c01ac6f6687fde46d6d06
//        $proxy = $this->getProxy();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://trum.vn/api/services/subspeed/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 300,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'authority: trum.vn',
                'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="99", "Microsoft Edge";v="99"',
                'accept: */*',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'x-requested-with: XMLHttpRequest',
                'sec-ch-ua-mobile: ?0',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.51 Safari/537.36 Edg/99.0.1150.39',
                'sec-ch-ua-platform: "Windows"',
                'origin: https://trum.vn',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://trum.vn/buff-sub-speed.html',
                'accept-language: en-US,en;q=0.9',
                'cookie: SL_G_WPT_TO=en; SL_GWPT_Show_Hide_tmp=1; SL_wptGlobTipTmp=1; _ga=GA1.1.1045907574.1647232217; TRUMVN=ede7321d2ded049f000d085e30962947; _ga_MHWLWBWLE7=GS1.1.1647254427.2.1.1647254455.0'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function runOrder($id)
    {
        $post_data = [
            'key' => getConfig('key_trumvb'),
            'order_id' => $id
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://trum.vn/api/services/subspeed/resume');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        $result = curl_exec($ch);
        curl_close($ch);
        try
        {
            $t = new TelegramService();
            $t->sendMessGroupCardToBotTelegram($result);
        }catch (\Exception $exception){}
        return json_decode($result);
    }
}
