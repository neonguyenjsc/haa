<?php


namespace App\Service\SaBomMoService;


use App\Http\Controllers\Traits\Lib;
use App\Models\Config;
use App\Service\Telegram\TelegramService;
use PHPUnit\Exception;

class SaBomMoService
{
    use Lib;

    public function callApi($data, $url = "https://customer.sabommo.net/api/index.php")
    {
        $config_token = Config::where('alias', 'sabommo')->first();
        $config_token = json_decode($config_token->value);
        $data['token'] = $config_token->token ?? null;
        $data['id_user'] = $config_token->id_user ?? null;
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
//                'api-token: Mw==5d9694b2ce698c7680adMTYzOTA5Nzc5Mw==',
                'Cookie: PHPSESSID=j8rte578stugs1nmka96ihg78j'
            ),
        ));

        $response = curl_exec($curl);
        $this->sendMessGroupCardToBotTelegram("sabommo" . $response);
        curl_close($curl);
        return json_decode($response);
    }

    public function buyV2($data)
    {
        $data = [
            'service_id' => $data['service_id'],
            'seeding_uid' => $data['seeding_uid'],
            'server_id' => $data['server_id'],
            'order_amount' => $data['order_amount'],
            'reaction_type' => $data['reaction_type'],
            'commend_need' => $data['commend_need'],
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sabommo.net/api/buff-order?access_token=' . getConfig('sabommo'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        try {
            $t = new TelegramService();
            $t->sendMessGroupCardToBotTelegram("sabommo => " . $response);
        } catch (Exception $exception) {
        }
        curl_close($curl);
        return json_decode($response);
    }

    public function buyVipV2($data, $url)
    {
        $data = [
            'service_id' => $data['service_id'],
            'seeding_uid' => $data['seeding_uid'],
            'server_id' => $data['server_id'],
            'month' => $data['month'],
            'reaction_type' => $data['reaction_type'],
            'commend_need' => $data['commend_need'],
            'order_amount' => $data['order_amount'],
        ];
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
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function callTrungGian($url, $data)
    {
        $form_data = [
            "url" => $url,
            "data" => $data,
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://45.77.43.222/api/trung-gian',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 240,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($form_data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function actionV2($data)
    {
        $url = 'https://sabommo.net/api/buff-order';
        $data['access_token'] = getConfig('sabommo');
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
                'Cookie: PHPSESSID=3u9ljd3mfef9km662lftlb0c89; lang=en-US'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function checkOrderV2($id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sabommo.net/api/buff-order?access_token=' . getConfig('sabommo') . '&action=check&order_ids=' . $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=1arsfb3e8g2ap56g0c45coub6f; lang=en-US'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function checkWarranty($id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sabommo.net/api/buff-order?access_token=' . getConfig('sabommo') . '&action=check_warranty&order_id=' . $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=1arsfb3e8g2ap56g0c45coub6f; lang=en-US'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
}
