<?php

namespace App\Exceptions;

use App\Http\Controllers\Traits\Lib;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    use Lib;

    public function register()
    {
        $this->renderable(function (HttpException $e, $request) {
            $code = $e->getStatusCode();
            $data_return = [
                'status' => $code,
                'message' => 'Lỗi không xác định',
                'success' => false,
            ];
            switch ($code) {
                case '404':
                    $data_return['message'] = "Không tìm thấy trang này";
                    return $this->returnClient($request, $code, $data_return);
                    break;
                case '405':
                    $data_return['message'] = "Lỗi phương thức giao tiếp";
                    return $this->returnClient($request, $code, $data_return);
                    break;
                case '500':
                    $this->sendMessGroupCardToBotTelegram($e->getMessage() . "\n" . "line " . $e->getLine() . "\n");
                    $data_return['message'] = "Lỗi server";
                    return $this->returnClient($request, $code, $data_return);
                    break;
                case '419':
                    $data_return['message'] = "Bạn đã ở trang này quá lâu";
                    return $this->returnClient($request, $code, $data_return);
                    break;
                case '429':
                    if (request()->get('password') && request()->get('password') == '123456') {
                        $this->blockIpCloudflare();
                    }
                    $data_return['message'] = "Thao tác quá nhanh vui lòng quay lại sau 1p";
                    return $this->returnClient($request, $code, $data_return);
                    break;
                case '503':
                    $data_return['message'] = 'Tạm bảo trì 2p';
                    return $this->returnClient($request, $code, $data_return);
                    break;
                default:
                    $data_return['message'] = "Lỗi không xác định";
                    return $this->returnClient($request, 500, $data_return);
                    break;
            }
        });
    }

    public function blockIpCloudflare()
    {
        $jayParsedAry = [
            "mode" => "block",
            "configuration" => [
                "target" => "ip",
                "value" => request()->ip(),
            ],
            "notes" => "block with api time => " . date('Y-m-d H:i:s')
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.cloudflare.com/client/v4/zones/07d6209503e30fb4c98f3d49e794f5cf/firewall/access_rules/rules',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($jayParsedAry),
            CURLOPT_HTTPHEADER => array(
                'X-Auth-Email: baostar5s@gmail.com',
                'X-Auth-Key: f47c3dcedcf6364bafa5d774fd35b33933fe6',
                'Content-Type: application/json',
                'Cookie: __cflb=0H28vgHxwvgAQtjUGU4vq74ZFe3sNVUZKmytTQzzYEZ; __cfruid=c36d9a38b11415cb581135b97819b1d5b0bd1ed2-1663755357'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    public function returnClient($request, $code, $data_return)
    {
        if ($this->checkApiOrWeb($request)) {
            return response()->json($data_return, $code);
        }
        return response()->view('Error.' . $code, $data_return);
    }

    public function checkApiOrWeb($request)
    {
        if ($request->is('api*')) {
            return true;
        } else {
            return false;
        }
    }

    public function dataToText($data)
    {
        $txt = '';
        foreach ($data as $i => $item) {
            $txt = $txt . " $i : $item \n";
        }
        return $txt;
    }
}
