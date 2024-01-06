<?php
use App\Http\Controllers\Ads\FacebookBotCommentController;
use App\Http\Controllers\Ads\FacebookCommentController;
use App\Http\Controllers\Ads\FacebookEyesController;
use App\Http\Controllers\Ads\FacebookFillController;
use App\Http\Controllers\Ads\FacebookFollowController;
use App\Http\Controllers\Ads\FacebookFreeController;
use App\Http\Controllers\Ads\FacebookLikeCommentController;
use App\Http\Controllers\Ads\FacebookLikeController;
use App\Http\Controllers\Ads\FacebookLikePageController;
use App\Http\Controllers\Ads\FacebookMemGroupController;
use App\Http\Controllers\Ads\FacebookPokeController;
use App\Http\Controllers\Ads\FacebookReviewController;
use App\Http\Controllers\Ads\FacebookSaleController;
use App\Http\Controllers\Ads\FacebookShareController;
use App\Http\Controllers\Ads\FacebookViewController;
use App\Http\Controllers\Ads\FacebookViewStoryController;
use App\Http\Controllers\Ads\FacebookVipCloneController;
use App\Http\Controllers\Ads\FacebookVipCommentController;
use App\Http\Controllers\Ads\FacebookVipEyesController;
use App\Http\Controllers\Ads\InstagramCommentController;
use App\Http\Controllers\Ads\InstagramFollowController;
use App\Http\Controllers\Ads\InstagramFreeController;
use App\Http\Controllers\Ads\InstagramLikeController;
use App\Http\Controllers\Ads\InstagramViewController;
use App\Http\Controllers\Ads\InstagramViewStoryController;
use App\Http\Controllers\Ads\InstagramVipLikeController;
use App\Http\Controllers\Ads\ProxyController;
use App\Http\Controllers\Ads\ShopeeFollowController;
use App\Http\Controllers\Ads\ShopeeLikeController;
use App\Http\Controllers\Ads\TelegramPostViewController;
use App\Http\Controllers\Ads\TikTokCommentController;
use App\Http\Controllers\Ads\TikTokFollowController;
use App\Http\Controllers\Ads\TikTokFreeController;
use App\Http\Controllers\Ads\TikTokLikeController;
use App\Http\Controllers\Ads\TikTokLiveStreamController;
use App\Http\Controllers\Ads\TikTokShareController;
use App\Http\Controllers\Ads\TikTokViewController;
use App\Http\Controllers\Ads\TikTokLiveDangMuaController;
use App\Http\Controllers\Ads\TikTokLiveHienThiController;
use App\Http\Controllers\Ads\TikTokShopController;
use App\Http\Controllers\Ads\YoutubeCommentController;
use App\Http\Controllers\Ads\YoutubeLikeController;
use App\Http\Controllers\Ads\YoutubeShareController;
use App\Http\Controllers\Ads\YoutubeSubController;
use App\Http\Controllers\Ads\YoutubeViewController;
use App\Http\Controllers\Ads\GoogleReviewController;
use App\Http\Controllers\Ads\TwitterLikeController;
use App\Http\Controllers\Ads\TwitterSubController;
use App\Http\Controllers\Baostar\BaostarController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\V2\HomeController;
use App\Http\Controllers\V2\LogsController;
use App\Http\Controllers\V2\PaymentController as PaymentV2Controller;
use App\Http\Controllers\V2\ProfileController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

//v2

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::get('/', function () {
    return response()->json(['status' => 200]);
});
Route::get('/list-b', function () {
    $filename = "E:\\xampp\\htdocs\\bt\\baostar_v2\\10kdonloi.txt";
    $fp = fopen($filename, "r");//mở file ở chế độ đọc

    $contents = fread($fp, filesize($filename));//đọc file
    $total = 0;
    $contents = json_decode($contents);
    foreach ($contents->data->content as $item) {
        if ($item->timestamp > 1661351157489) {
            echo date('Y-m-d H:i:s', $item->timestamp / 1000) . " => " . $item->id . " => " . $item->fbUid . " => " . $item->paidAmount;
            echo "<br>";
            $total = $total + $item->paidAmount;
        }
    }
    echo $total;
    exit();
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://2fa.shop/api/buy-history?page=0&size=10000&fulfillment=1',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Connection: keep-alive',
            'Accept: application/json, text/plain, */*',
            'TK: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJodXluaHF1b2NkYWkiLCJzY29wZXMiOiJbUk9MRV9VU0VSLCBST0xFX1dIT0xFU0FMRV0iLCJpYXQiOjE2NjIyMTY0NzgsImV4cCI6MTY2NDgwODQ3OH0.T5HaCvJvtwrujA_mcv7gdZbKVio0KLvYAVTuih1BB6r8hSOZumFf2hsOQy-EhKTDVOdnoo4yhhWhdE1_m3t9kQ',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
            'Referer: http://2fa.shop/',
            'Accept-Language: vi,en-US;q=0.9,en;q=0.8',
            'Cookie: SL_G_WPT_TO=en; SL_GWPT_Show_Hide_tmp=1; SL_wptGlobTipTmp=1; JSESSIONID=8CB64F1B6387B5A46ADAAA77828AF932; TK=eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJodXluaHF1b2NkYWkiLCJzY29wZXMiOiJbUk9MRV9VU0VSLCBST0xFX1dIT0xFU0FMRV0iLCJpYXQiOjE2NjIyMTY0NzgsImV4cCI6MTY2NDgwODQ3OH0.T5HaCvJvtwrujA_mcv7gdZbKVio0KLvYAVTuih1BB6r8hSOZumFf2hsOQy-EhKTDVOdnoo4yhhWhdE1_m3t9kQ'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $response = json_decode($response);
    $data = $response->data->content;
    foreach ($data as $item) {
        //647501
        dd($item);
    }
});

Route::get('/callback-nap-the-cao', [PaymentController::class, 'callBack']);
Route::post('/callback-nap-the-cao-v2', [PaymentController::class, 'callBackV2']);
Route::get('/get-all-pricesssssssss', [SiteController::class, 'getAllPrices']);
Route::get('/get-all-menuuuuuuuuuuu', [SiteController::class, 'getMenu']);
Route::post('/check-cookie', [FacebookPokeController::class, 'apiCheckCookie']);
Route::post('/webhook-telegram', [WebhookController::class, 'webHookTelegram']);
Route::group(['prefix' => '/web-hook'], function () {
    Route::post('/vcb', [WebhookController::class, 'main']);
    Route::post('/baostar', [WebhookController::class, 'baostar']);
});
Route::get('/history', [SiteController::class, 'history']);
Route::get('/get-logs-card', [SiteController::class, 'logsCard']);
Route::post('/convert-uid', [Controller::class, 'convertUID']);
Route::middleware(['check_key_user', 'ddos'])->group(function () {
    Route::post('/logs-order', [SiteController::class, 'logsOrder']);
    Route::post('/payment/card', [PaymentController::class, 'cardApi']);
    Route::post('/price-per-master', [SiteController::class, 'getPricesWithId']);
    Route::get('/prices', [SiteController::class, 'getListPrice']);

    Route::group(['prefix' => '/facebook-like-gia-re'], function () {
        Route::post('/buy', [FacebookSaleController::class, 'buyApi']);
        Route::get('/remove/{id}', [FacebookSaleController::class, 'removeApi']);
        Route::get('/check/{id}', [FacebookSaleController::class, 'checkOrderApi']);
        Route::get('/continue/{id}', [FacebookSaleController::class, 'runOrderApi']);
        Route::get('/warranty/{id}', [FacebookSaleController::class, 'warrantyApi']);
    });
    Route::group(['prefix' => '/facebook-free'], function () {
        Route::post('/buy', [FacebookFreeController::class, 'buyApi']);
    });
    Route::group(['prefix' => '/instagram-free'], function () {
        Route::post('/buy', [InstagramFreeController::class, 'buyApi']);
    });
    Route::group(['prefix' => '/tiktok-free'], function () {
        Route::post('/buy', [TikTokFreeController::class, 'buyApi']);
    });
    Route::group(['prefix' => '/facebook-like-chat-luong'], function () {
        Route::post('/buy', [FacebookLikeController::class, 'buyApi']);
    });
    Route::group(['prefix' => '/facebook-like-binh-luan'], function () {
        Route::post('/buy', [FacebookLikeCommentController::class, 'buyApi']);
    });

    Route::group(['prefix' => '/facebook-binh-luan'], function () {
        Route::post('/buy', [FacebookCommentController::class, 'buyApi']);
        Route::get('/continue/{id}', [FacebookCommentController::class, 'runOrderApi']);
        Route::get('/check/{id}', [FacebookCommentController::class, 'checkOrderApi']);
        Route::get('/remove/{id}', [FacebookCommentController::class, 'removeApi']);
        Route::get('/warranty/{id}', [FacebookSaleController::class, 'warrantyApi']);
    });

    Route::group(['prefix' => '/facebook-follow'], function () {
        Route::post('/buy', [FacebookFollowController::class, 'buyApi']);
        Route::get('/remove/{id}', [FacebookFollowController::class, 'removeApi']);
        Route::get('/check/{id}', [FacebookFollowController::class, 'checkOrderApi']);
        Route::get('/continue/{id}', [FacebookFollowController::class, 'runOrderApi']);
        Route::get('/warranty/{id}', [FacebookSaleController::class, 'warrantyApi']);
    });

    Route::group(['prefix' => '/facebook-like-page'], function () {
        Route::post('/buy', [FacebookLikePageController::class, 'buyApi']);
        Route::get('/remove/{id}', [FacebookLikePageController::class, 'removeApi']);
        Route::get('/check/{id}', [FacebookLikePageController::class, 'checkOrderApi']);
        Route::get('/continue/{id}', [FacebookLikePageController::class, 'runOrderApi']);
        Route::get('/warranty/{id}', [FacebookSaleController::class, 'warrantyApi']);
    });
    Route::group(['prefix' => '/facebook-mem-group'], function () {
        Route::post('/buy', [FacebookMemGroupController::class, 'buyApi']);
        Route::post('/get-uid', [FacebookMemGroupController::class, 'getUid']);
        Route::get('/remove/{id}', [FacebookMemGroupController::class, 'removeApi']);
        Route::get('/warranty/{id}', [FacebookSaleController::class, 'warrantyApi']);
        Route::get('/continue/{id}', [FacebookSaleController::class, 'runOrderApi']);
        Route::get('/check/{id}', [FacebookMemGroupController::class, 'checkOrderApi']);
    });
    Route::group(['prefix' => '/facebook-view-video'], function () {
        Route::post('/buy', [FacebookViewController::class, 'buyApi']);
        Route::get('/continue/{id}', [FacebookViewController::class, 'runOrderApi']);
    });
    Route::group(['prefix' => '/facebook-eyes'], function () {
        Route::post('/buy', [FacebookEyesController::class, 'buyApi']);
    });

    Route::group(['prefix' => '/facebook-share'], function () {
        Route::post('/buy', [FacebookShareController::class, 'buyApi']);
        Route::get('/continue/{id}', [FacebookShareController::class, 'runOrderApi']);
    });

    Route::group(['prefix' => '/facebook-view-story'], function () {
        Route::post('/buy', [FacebookViewStoryController::class, 'buyApi']);
        Route::get('/continue/{id}', [FacebookViewStoryController::class, 'runOrderApi']);
    });

    Route::group(['prefix' => '/facebook-poke'], function () {
        Route::post('/buy', [FacebookPokeController::class, 'buyApi']);
    });
    Route::group(['prefix' => '/facebook-fillter'], function () {
        Route::post('/buy', [FacebookFillController::class, 'buyApi']);
    });

    Route::group(['prefix' => '/facebook-review'], function () {
        Route::post('/buy', [FacebookReviewController::class, 'buyApi']);
        Route::get('/continue/{id}', [FacebookViewStoryController::class, 'runOrderApi']);
    });
    Route::group(['prefix' => '/facebook-vip-clone'], function () {
        Route::post('/buy', [FacebookVipCloneController::class, 'buyApi']);
        Route::post('/renew/{id}', [FacebookVipCloneController::class, 'renewApi']);
    });
    Route::group(['prefix' => '/facebook-vip-comment'], function () {
        Route::post('/buy', [FacebookVipCommentController::class, 'buyApi']);
        Route::post('/change-comment/{id}', [FacebookVipCommentController::class, 'changeCommentApi']);
    });
    Route::group(['prefix' => '/facebook-vip-eyes'], function () {
        Route::post('/buy', [FacebookVipEyesController::class, 'buyApi']);
    });
    Route::group(['prefix' => '/facebook-proxy'], function () {
        Route::post('/buy', [ProxyController::class, 'buyApi']);
    });

    Route::group(['prefix' => '/facebook-bot-comment'], function () {
        Route::post('/buy', [FacebookBotCommentController::class, 'buyApi']);
        Route::post('/update', [FacebookBotCommentController::class, 'updateApi']);
    });

    /*instagram*/

    Route::group(['prefix' => '/instagram-like'], function () {
        Route::post('/buy', [InstagramLikeController::class, 'buyApi']);
        Route::get('/check/{id}', [InstagramLikeController::class, 'checkOrderApi']);
        Route::get('/remove/{id}', [InstagramLikeController::class, 'removeApi']);
    });

    Route::group(['prefix' => '/instagram-follow'], function () {
        Route::post('/buy', [InstagramFollowController::class, 'buyApi']);
        Route::get('/check/{id}', [InstagramFollowController::class, 'checkOrderApi']);
        Route::get('/remove/{id}', [InstagramFollowController::class, 'removeApi']);
    });

    Route::group(['prefix' => '/instagram-view'], function () {
        Route::post('/buy', [InstagramViewController::class, 'buyApi']);
        Route::get('/check/{id}', [InstagramViewController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/instagram-comment'], function () {
        Route::post('/buy', [InstagramCommentController::class, 'buyApi']);
        Route::get('/check/{id}', [InstagramCommentController::class, 'checkOrderApi']);
    });
    Route::group(['prefix' => '/instagram-view-story'], function () {
        Route::post('/buy', [InstagramViewStoryController::class, 'buyApi']);
        Route::get('/check/{id}', [InstagramViewStoryController::class, 'checkOrderApi']);
    });
    Route::group(['prefix' => '/instagram-vip-like'], function () {
        Route::post('/buy', [InstagramVipLikeController::class, 'buyApi']);
    });
    /*tiktok*/

    Route::group(['prefix' => '/tiktok-like'], function () {
        Route::post('/buy', [TikTokLikeController::class, 'buyApi']);
        Route::get('/check/{id}', [TikTokLikeController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/tiktok-follow'], function () {
        Route::post('/buy', [TikTokFollowController::class, 'buyApi']);
        Route::get('/check/{id}', [TikTokFollowController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/tiktok-view'], function () {
        Route::post('/buy', [TikTokViewController::class, 'buyApi']);
        Route::get('/check/{id}', [TikTokViewController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/tiktok-share'], function () {
        Route::post('/buy', [TikTokShareController::class, 'buyApi']);
        Route::get('/check/{id}', [TikTokShareController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/tiktok-comment'], function () {
        Route::post('/buy', [TikTokCommentController::class, 'buyApi']);
        Route::get('/check/{id}', [TikTokCommentController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/tiktok-live'], function () {
        Route::post('/buy', [TikTokLiveStreamController::class, 'buyApi']);
        Route::get('/check/{id}', [TikTokLiveStreamController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/tiktok-shop-mua-hang'], function () {
        Route::post('/buy', [TikTokShopController::class, 'buyApi']);
        Route::get('/check/{id}', [TikTokShopController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/tiktok-shop-stream-hien-thi-mua-hang'], function () {
        Route::post('/buy', [TikTokLiveHienThiController::class, 'buyApi']);
        Route::get('/check/{id}', [TikTokLiveHienThiController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/tiktok-shop-stream-dang-mua-hang'], function () {
        Route::post('/buy', [TikTokLiveDangMuaController::class, 'buyApi']);
        Route::get('/check/{id}', [TikTokLiveDangMuaController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/youtube-like'], function () {
        Route::post('/buy', [YoutubeLikeController::class, 'buyApi']);
        Route::get('/check/{id}', [YoutubeLikeController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/youtube-sub'], function () {
        Route::post('/buy', [YoutubeSubController::class, 'buyApi']);
        Route::get('/check/{id}', [YoutubeSubController::class, 'checkOrderApi']);
    });
    Route::group(['prefix' => '/youtube-follow'], function () {
        Route::post('/buy', [YoutubeSubController::class, 'buyApi']);
        Route::get('/check/{id}', [YoutubeSubController::class, 'checkOrderApi']);
    });
    Route::group(['prefix' => '/youtube-view'], function () {
        Route::post('/buy', [YoutubeViewController::class, 'buyApi']);
        Route::get('/check/{id}', [YoutubeViewController::class, 'checkOrderApi']);
    });
    Route::group(['prefix' => '/youtube-comment'], function () {
        Route::post('/buy', [YoutubeCommentController::class, 'buyApi']);
        Route::get('/check/{id}', [YoutubeCommentController::class, 'checkOrderApi']);
    });
    Route::group(['prefix' => '/youtube-share'], function () {
        Route::post('/buy', [YoutubeShareController::class, 'buyApi']);
        Route::get('/check/{id}', [YoutubeShareController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/shopee-like'], function () {
        Route::post('/buy', [ShopeeLikeController::class, 'buyApi']);
    });

    Route::group(['prefix' => '/telegram-post-view'], function () {
        Route::post('/buy', [TelegramPostViewController::class, 'buyApi']);
        Route::get('/check/{id}', [TelegramPostViewController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/shopee-follow'], function () {
        Route::post('/buy', [ShopeeFollowController::class, 'buyApi']);
        Route::post('/convert-uid', [ShopeeFollowController::class, 'convertUid']);
    });
    //tw

    Route::group(['prefix' => '/twitter-like'], function () {
        Route::post('/buy', [TwitterLikeController::class, 'buyApi']);
        Route::get('/check/{id}', [TwitterLikeController::class, 'checkOrderApi']);
    });
    Route::group(['prefix' => '/twitter-sub'], function () {
        Route::post('/buy', [TwitterSubController::class, 'buyApi']);
        Route::get('/check/{id}', [TwitterSubController::class, 'checkOrderApi']);
    });

    Route::group(['prefix' => '/review-google-map'], function () {
        Route::post('/buy', [GoogleReviewController::class, 'buyApi']);
    });
});
Route::get('/me-v1', [SiteController::class, 'getMe']);
Route::get('/log-order-agency-lv2-gtq', [LogsController::class, 'logOrderLv2']);
Route::get('/log-order-agency-followcheap', [LogsController::class, 'logOrderFollowCheap']);
Route::post('/log-order-agency-followcheap', [LogsController::class, 'logOrderFollowCheap']);
Route::group(['prefix' => 'v2',], function () {
    Route::post('/login', [ProfileController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/register', [ProfileController::class, 'register']);
    Route::get('/get-system-notify', [HomeController::class, 'getNotifySystem']);
    Route::get('/get-menu-home', [HomeController::class, 'getCategoryHome']);
    Route::get('/get-payment', [PaymentV2Controller::class, 'getListPayment']);
    Route::get('/get-package', [HomeController::class, 'getPackage']);
    Route::get('/get-prices-follow-cheap', [HomeController::class, 'getPriceFollowCheap']);
    Route::middleware(['check_key_user', 'ddos'])->group(function () {
        Route::get('/me', [ProfileController::class, 'me']);
        Route::get('/log', [LogsController::class, 'index']);
        Route::get('/log-order', [LogsController::class, 'logOrder']);
        Route::post('/update-profile', [ProfileController::class, 'update']);
        Route::prefix('/card')->group(function () {
            Route::get('/get-rate', [PaymentV2Controller::class, 'getRatePaymentCard']);
            Route::get('/get-log', [PaymentV2Controller::class, 'getLogs']);
            Route::get('/get-log-card', [PaymentV2Controller::class, 'getLogsCard']);
            Route::post('/doi-card', [PaymentController::class, 'cardApi']);
        });
        Route::get('/get-notify', [HomeController::class, 'getNotifyUser']);
    });
});
Route::prefix('/baostar')->middleware('key_baostar')->group(function () {
    Route::get('/list-order', [BaostarController::class, 'index']);
    Route::post('/update/{id}', [BaostarController::class, 'update']);
});
