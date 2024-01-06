<?php


namespace App\Service\FarmApi;


use App\Http\Controllers\Traits\Lib;
use App\Models\V2\Ads\Telegram;

class FarmService
{
    use Lib;

    public function addOrder($data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_FARM . '/seller/order/add',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('url' => 'https://facebook.com/' . $data['object_id'], 'token' => getConfig('key_farm'), 'uid' => $data['object_id'], 'product_id' => $data['product_id'], 'quantity' => $data['quantity'], 'data_comments' => $data["data_comments"] ?? '', 'note' => $data['notes'], 'confirm' => 1, 'comments' => $data['comments'] ?? '', 'reaction' => $data['reaction'] ?? false),
        ));
        $response = curl_exec($curl);
        $this->sendMessGroupCardToBotTelegram("like88 => " . $response);
        curl_close($curl);
        if (strpos($response, "<title>500") || $response == '' || !$response) {
            return json_decode('{"status":400,"success":false,"message":"Tạo thất bại lỗi #1"}');
        }
        return json_decode($response);
    }

    public function addVip($data)
    {
        $p_data = array(
            'token' => getConfig('key_farm'),
            'uid' => $data['object_id'],
            'product_name' => $data['product_name'],
            'product_speed' => 'low',
            'quantity' => $data['quantity'],
            'days' => $data['days'],
            'confirm' => 1,
            'note' => $data['notes'] ?? ''
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_FARM . '/seller/ordervip/add',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $p_data,
        ));
        $response = curl_exec($curl);
        $this->sendMessGroupCardToBotTelegram("like88 vip => " . $response);
        curl_close($curl);
        return json_decode($response);
    }

    public function addVipComment($data)
    {
        $p_data = array(
            'token' => getConfig('key_farm'),
            'uid' => $data['object_id'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'days' => $data['days'],
            'confirm' => 1,
            'product_speed' => 'low',
            'note' => $data['notes'] ?? '',
            'comments' => $data['comments']
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_FARM . '/seller/ordervip/add',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $p_data,
        ));
        $response = curl_exec($curl);
        $this->sendMessGroupCardToBotTelegram("like88 vip => " . $response);
        curl_close($curl);
        return json_decode($response);
    }

    public function checkOrder($data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_FARM . '/seller/order/detail',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('token' => getConfig('key_farm'), 'order_id' => $data['orders_id']),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function actionOrder($data)
    {
        $action = $data['action'] ?? '';
        if ($data['action'] == 'remove') {
            $action = 'cancelled';
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_FARM . '/seller/order/confirm',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('token' => getConfig('key_farm'), 'order_id' => $data['orders_id'], 'action' => $action),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function actionOrderVip($data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => DOMAIN_FARM . '/seller/ordervip/confirm',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('token' => getConfig('key_farm'), 'action' => 'cancelled', 'order_id' => $data['orders_id']),
            CURLOPT_HTTPHEADER => array(
                'Cookie: ci_session=capc2g4tsbafj4e4cjaq0ua9oocmp7la'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
}
