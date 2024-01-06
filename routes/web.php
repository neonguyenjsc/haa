<?php

use App\Http\Controllers\Admin\AutoPaymentController;
use App\Http\Controllers\Admin\CardConfigController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NotifyController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentConfigController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\RatingController as RatingAdmin;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\UserController;
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
use App\Http\Controllers\Ads\PaymentCardController;
use App\Http\Controllers\Ads\ProxyController;
use App\Http\Controllers\Ads\ShopeeFollowController;
use App\Http\Controllers\Ads\ShopeeLikeController;
use App\Http\Controllers\Ads\TelegramPostViewController;
use App\Http\Controllers\Ads\TikTokCommentController;
use App\Http\Controllers\Ads\TikTokFollowController;
use App\Http\Controllers\Ads\TikTokFreeController;
use App\Http\Controllers\Ads\TikTokLikeController;
use App\Http\Controllers\Ads\TikTokShareController;
use App\Http\Controllers\Ads\TikTokViewController;
use App\Http\Controllers\Ads\YoutubeCommentController;
use App\Http\Controllers\Ads\YoutubeLikeController;
use App\Http\Controllers\Ads\YoutubeLiveStreamController;
use App\Http\Controllers\Ads\YoutubeShareController;
use App\Http\Controllers\Ads\YoutubeSubController;
use App\Http\Controllers\Ads\YoutubeViewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});
//Route::get('/danh-sach-don-tiktok', function () {
//    $date = [
//        '2022-07-31 00:00:00',
//        '2022-08-10 23:59:59',
//    ];
//    $list = TikTok::whereIn('package_name', [
//            'tiktok_follow_sv6'
////        'tiktok_like_v3'
//    ])->whereBetween('created_at', $date)->get();
//    return view('welcome', ['data' => $list]);
//});
Route::group(['middleware' => 'ddos'], function () {
    Route::get('/dang-nhap', [AuthController::class, 'viewLogin'])->middleware('throttle:10,1');
    Route::get('/dang-ky', [AuthController::class, 'viewRegister'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::get('/login-with-username', [AuthController::class, 'loginWithUsername'])->middleware('throttle:10,1');
Route::get('/convert-mulpti', function () {
    return view('convert');
});
Route::post('/convert-mulpti', [\App\Http\Controllers\Controller::class, 'convertMulti']);
Route::get('/convert-ip', function () {
    return view('convert_ip');
});
Route::post('/convert-ip', function () {
    $data = request()->data;
    $data = explode("\n", $data);
    foreach ($data as $item) {
        $regex = '/([0-9]{1,4}\s)/';
        preg_match($regex, $item, $data);
        if ($data[0]) {
            $ip = str_replace($data[0], "", $item);
            $ip = str_replace("\r", "", $ip);
            if (!in_array($ip, [
                '0.0.0.0',
                '139.180.209.150',
                '14.225.254.185'
            ])) {
                echo "csf -d " . $ip . "<br>";
            }
        }
    }
});
//Route::
Route::middleware(['check_user'])->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/tai-khoan', [ProfileController::class, 'index']);
    Route::post('/tai-khoan/update', [ProfileController::class, 'update']);
    Route::get('/lich-su', [HistoryController::class, 'index']);
    Route::get('/nap-tien', [PaymentController::class, 'index']);
    Route::get('/api-tich-hop', function () {
        return view('Api.index');
    });
    Route::post('/nap-tien/card', [PaymentController::class, 'card']);

    Route::get('/tich-hop', [SiteController::class, 'index']);

    Route::group(['prefix' => 'rating'], function () {
        Route::get('/', [RatingController::class, 'index']);
        Route::post('/add', [RatingController::class, 'rating']);
    });
    /*ads*/
    Route::group(['prefix' => '/facebook-free'], function () {
        Route::get('/', [FacebookFreeController::class, 'index']);
        Route::get('/nhat-ky', [FacebookFreeController::class, 'history']);
        Route::post('/buy', [FacebookFreeController::class, 'buy']);
    });
    Route::group(['prefix' => '/instagram-free'], function () {
        Route::get('/', [InstagramFreeController::class, 'index']);
        Route::get('/nhat-ky', [InstagramFreeController::class, 'history']);
        Route::post('/buy', [InstagramFreeController::class, 'buy']);
    });
    Route::group(['prefix' => '/tiktok-free'], function () {
        Route::get('/', [TikTokFreeController::class, 'index']);
        Route::get('/nhat-ky', [TikTokFreeController::class, 'history']);
        Route::post('/buy', [TikTokFreeController::class, 'buy']);
    });
    //    Route::group()
    Route::group(['prefix' => '/facebook-like-gia-re'], function () {
        Route::get('/', [FacebookSaleController::class, 'index']);
        Route::get('/remove/{id}', [FacebookSaleController::class, 'remove']);
        Route::get('/nhat-ky', [FacebookSaleController::class, 'history'])->middleware('throttle:5,1');
        Route::get('/check-order/{id}', [FacebookSaleController::class, 'checkOrder'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookSaleController::class, 'buy']);
        Route::get('/run-order/{id}', [FacebookLikePageController::class, 'runOrder'])->middleware('throttle:5,1');
    });
    Route::group(['prefix' => '/facebook-like-chat-luong'], function () {
        Route::get('/', [FacebookLikeController::class, 'index']);
        Route::get('/nhat-ky', [FacebookLikeController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookLikeController::class, 'buy']);
    });
    Route::group(['prefix' => '/facebook-like-binh-luan'], function () {
        Route::get('/', [FacebookLikeCommentController::class, 'index']);
        Route::get('/nhat-ky', [FacebookLikeCommentController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookLikeCommentController::class, 'buy']);
    });

    Route::group(['prefix' => '/facebook-binh-luan'], function () {
        Route::get('/', [FacebookCommentController::class, 'index']);
        Route::get('/remove/{id}', [FacebookCommentController::class, 'remove']);
        Route::get('/nhat-ky', [FacebookCommentController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookCommentController::class, 'buy']);
    });

    Route::group(['prefix' => '/facebook-follow'], function () {
        Route::get('/', [FacebookFollowController::class, 'index']);
        Route::get('/remove/{id}', [FacebookFollowController::class, 'remove']);
        Route::get('/warranty/{id}', [FacebookFollowController::class, 'warranty']);
        Route::get('/nhat-ky', [FacebookFollowController::class, 'history'])->middleware('throttle:5,1');
        Route::get('/check-order/{id}', [FacebookFollowController::class, 'checkOrder'])->middleware('throttle:5,1');
        Route::get('/run-order/{id}', [FacebookFollowController::class, 'runOrder'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookFollowController::class, 'buy']);
    });

    Route::group(['prefix' => '/facebook-like-page'], function () {
        Route::get('/', [FacebookLikePageController::class, 'index']);
        Route::get('/remove/{id}', [FacebookLikePageController::class, 'remove']);
        Route::get('/warranty/{id}', [FacebookLikePageController::class, 'warranty']);
        Route::get('/nhat-ky', [FacebookLikePageController::class, 'history'])->middleware('throttle:5,1');
        Route::get('/check-order/{id}', [FacebookLikePageController::class, 'checkOrder'])->middleware('throttle:5,1');
        Route::get('/run-order/{id}', [FacebookLikePageController::class, 'runOrder'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookLikePageController::class, 'buy']);
    });
    Route::group(['prefix' => '/facebook-mem-group'], function () {
        Route::get('/', [FacebookMemGroupController::class, 'index']);
        Route::get('/nhat-ky', [FacebookMemGroupController::class, 'history'])->middleware('throttle:5,1');
        Route::get('/remove/{id}', [FacebookMemGroupController::class, 'remove']);
        Route::post('/buy', [FacebookMemGroupController::class, 'buy']);
    });
    Route::group(['prefix' => '/facebook-view-video'], function () {
        Route::get('/', [FacebookViewController::class, 'index']);
        Route::get('/nhat-ky', [FacebookViewController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookViewController::class, 'buy']);
    });
    Route::group(['prefix' => '/facebook-eyes'], function () {
        Route::get('/', [FacebookEyesController::class, 'index']);
        Route::get('/nhat-ky', [FacebookEyesController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookEyesController::class, 'buy']);
    });

    Route::group(['prefix' => '/facebook-share'], function () {
        Route::get('/', [FacebookShareController::class, 'index']);
        Route::get('/nhat-ky', [FacebookShareController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookShareController::class, 'buy']);
    });

    Route::group(['prefix' => '/facebook-view-story'], function () {
        Route::get('/', [FacebookViewStoryController::class, 'index']);
        Route::get('/nhat-ky', [FacebookViewStoryController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookViewStoryController::class, 'buy']);
    });
    Route::group(['prefix' => '/facebook-review'], function () {
        Route::get('/', [FacebookReviewController::class, 'index']);
        Route::get('/nhat-ky', [FacebookReviewController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookReviewController::class, 'buy']);
    });

    Route::group(['prefix' => '/facebook-bot-comment'], function () {
        Route::get('/', [FacebookBotCommentController::class, 'index']);
        Route::get('/nhat-ky', [FacebookBotCommentController::class, 'history'])->middleware('throttle:5,1');
        Route::get('/detail/{id}', [FacebookBotCommentController::class, 'detail']);
        Route::post('/buy', [FacebookBotCommentController::class, 'buy']);
        Route::post('/update', [FacebookBotCommentController::class, 'updateWeb']);
    });
    Route::group(['prefix' => '/facebook-proxy'], function () {
        Route::get('/', [ProxyController::class, 'index']);
        Route::get('/nhat-ky', [ProxyController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [ProxyController::class, 'buy']);
    });
    Route::group(['prefix' => '/facebook-vip-clone'], function () {
        Route::get('/', [FacebookVipCloneController::class, 'index']);
        Route::get('/remove/{id}', [FacebookVipCloneController::class, 'remove']);
        Route::get('/nhat-ky', [FacebookVipCloneController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookVipCloneController::class, 'buy']);
    });
    Route::group(['prefix' => '/facebook-vip-comment'], function () {
        Route::get('/', [FacebookVipCommentController::class, 'index']);
        Route::get('/nhat-ky', [FacebookVipCommentController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookVipCommentController::class, 'buy']);
    });
    Route::group(['prefix' => '/facebook-vip-eyes'], function () {
        Route::get('/', [FacebookVipEyesController::class, 'index']);
        Route::get('/nhat-ky', [FacebookVipEyesController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookVipEyesController::class, 'buy']);
    });
    Route::group(['prefix' => '/facebook-poke'], function () {
        Route::get('/', [FacebookPokeController::class, 'index']);
        Route::get('/nhat-ky', [FacebookPokeController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookPokeController::class, 'buy']);
    });
    Route::group(['prefix' => '/facebook-fillter'], function () {
        Route::get('/', [FacebookFillController::class, 'index']);
        Route::get('/nhat-ky', [FacebookFillController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [FacebookFillController::class, 'buy']);
    });
    /*instagram*/

    Route::group(['prefix' => '/instagram-like'], function () {
        Route::get('/', [InstagramLikeController::class, 'index']);
        Route::get('/nhat-ky', [InstagramLikeController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [InstagramLikeController::class, 'buy']);
    });

    Route::group(['prefix' => '/instagram-follow'], function () {
        Route::get('/', [InstagramFollowController::class, 'index']);
        Route::get('/nhat-ky', [InstagramFollowController::class, 'history'])->middleware('throttle:5,1');
        Route::get('/remove/{id}', [InstagramFollowController::class, 'remove'])->middleware('throttle:5,1');
        Route::post('/buy', [InstagramFollowController::class, 'buy']);
    });

    Route::group(['prefix' => '/instagram-view'], function () {
        Route::get('/', [InstagramViewController::class, 'index']);
        Route::get('/nhat-ky', [InstagramViewController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [InstagramViewController::class, 'buy']);
    });

    Route::group(['prefix' => '/instagram-comment'], function () {
        Route::get('/', [InstagramCommentController::class, 'index']);
        Route::get('/nhat-ky', [InstagramCommentController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [InstagramCommentController::class, 'buy']);
    });
    Route::group(['prefix' => '/instagram-view-story'], function () {
        Route::get('/', [InstagramViewStoryController::class, 'index']);
        Route::get('/nhat-ky', [InstagramViewStoryController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [InstagramViewStoryController::class, 'buy']);
    });

    Route::group(['prefix' => '/instagram-vip-like'], function () {
        Route::get('/', [InstagramVipLikeController::class, 'index']);
        Route::get('/nhat-ky', [InstagramVipLikeController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [InstagramVipLikeController::class, 'buy']);
    });
    /*tiktok*/

    Route::group(['prefix' => '/tiktok-like'], function () {
        Route::get('/', [TikTokLikeController::class, 'index']);
        Route::get('/nhat-ky', [TikTokLikeController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [TikTokLikeController::class, 'buy']);
    });

    Route::group(['prefix' => '/tiktok-comment'], function () {
        Route::get('/', [TikTokCommentController::class, 'index']);
        Route::get('/nhat-ky', [TikTokCommentController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [TikTokCommentController::class, 'buy']);
    });

    Route::group(['prefix' => '/tiktok-follow'], function () {
        Route::get('/', [TikTokFollowController::class, 'index']);
        Route::get('/nhat-ky', [TikTokFollowController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [TikTokFollowController::class, 'buy']);
    });

    Route::group(['prefix' => '/tiktok-view'], function () {
        Route::get('/', [TikTokViewController::class, 'index']);
        Route::get('/nhat-ky', [TikTokViewController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [TikTokViewController::class, 'buy']);
    });

    Route::group(['prefix' => '/tiktok-share'], function () {
        Route::get('/', [TikTokShareController::class, 'index']);
        Route::get('/nhat-ky', [TikTokShareController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [TikTokShareController::class, 'buy']);
    });

    Route::group(['prefix' => '/youtube-like'], function () {
        Route::get('/', [YoutubeLikeController::class, 'index']);
        Route::get('/nhat-ky', [YoutubeLikeController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [YoutubeLikeController::class, 'buy']);
    });
    Route::group(['prefix' => '/youtube-live'], function () {
        Route::get('/', [YoutubeLiveStreamController::class, 'index']);
        Route::get('/nhat-ky', [YoutubeLiveStreamController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [YoutubeLiveStreamController::class, 'buy']);
    });
    Route::group(['prefix' => '/youtube-share'], function () {
        Route::get('/', [YoutubeShareController::class, 'index']);
        Route::get('/nhat-ky', [YoutubeShareController::class, 'history'])->middleware('throttle:5,1');
        Route::post('/buy', [YoutubeShareController::class, 'buy']);
    });
    Route::group(['prefix' => '/youtube-sub'], function () {
        Route::get('/', [YoutubeSubController::class, 'index']);
        Route::get('/nhat-ky', [YoutubeSubController::class, 'history']);
        Route::post('/buy', [YoutubeSubController::class, 'buy']);
    });
    Route::group(['prefix' => '/youtube-view'], function () {
        Route::get('/', [YoutubeViewController::class, 'index']);
        Route::get('/nhat-ky', [YoutubeViewController::class, 'history']);
        Route::post('/buy', [YoutubeViewController::class, 'buy']);
    });
    Route::group(['prefix' => '/youtube-comment'], function () {
        Route::get('/', [YoutubeCommentController::class, 'index']);
        Route::get('/nhat-ky', [YoutubeCommentController::class, 'history']);
        Route::post('/buy', [YoutubeCommentController::class, 'buy']);
    });

    Route::group(['prefix' => '/shopee-like'], function () {
        Route::get('/', [ShopeeLikeController::class, 'index']);
        Route::get('/nhat-ky', [ShopeeLikeController::class, 'history']);
        Route::post('/buy', [ShopeeLikeController::class, 'buy']);
    });

    Route::group(['prefix' => '/shopee-follow'], function () {
        Route::get('/', [ShopeeFollowController::class, 'index']);
        Route::get('/nhat-ky', [ShopeeFollowController::class, 'history']);
        Route::post('/buy', [ShopeeFollowController::class, 'buy']);
    });

    Route::group(['prefix' => '/telegram-post-view'], function () {
        Route::get('/', [TelegramPostViewController::class, 'index']);
        Route::get('/nhat-ky', [TelegramPostViewController::class, 'history']);
        Route::post('/buy', [TelegramPostViewController::class, 'buy']);
    });
    Route::group(['prefix' => '/nap-tien-dien-thoai'], function () {
        Route::get('/', [PaymentCardController::class, 'index']);
        Route::get('/nhat-ky', [PaymentCardController::class, 'history']);
        Route::post('/buy', [PaymentCardController::class, 'buy']);
    });
});

Route::middleware(['check_admin'])->group(function () {
    Route::prefix('/admin')->group(function () {
        Route::prefix('/khach-hang')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/add-coin/{id}', [UserController::class, 'addCoinView']);
            Route::get('/reset-pass/{id}', [UserController::class, 'resetPass']);
            Route::get('/{id}', [UserController::class, 'update']);
            Route::post('/update', [UserController::class, 'updateAction']);
            Route::post('/add-coin', [UserController::class, 'addCoin']);
        });

        Route::group(['prefix' => 'static'], function () {
            Route::get('/', [StatisticsController::class, 'index']);
            Route::get('/detail', [StatisticsController::class, 'detail']);
            Route::get('/log', [StatisticsController::class, 'log']);
            Route::get('/detail-create-jobs', [StatisticsController::class, 'detailCreateJobs']);
        });

        Route::prefix('/danh-muc')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/detail/{id}', [CategoryController::class, 'detail']);
            Route::post('/update', [CategoryController::class, 'update']);
        });

        Route::prefix('/rating')->group(function () {
            Route::get('/', [RatingAdmin::class, 'index']);
        });

        Route::prefix('/auto-payment')->group(function () {
            Route::get('/', [AutoPaymentController::class, 'index']);
            Route::get('/momo', [AutoPaymentController::class, 'momo']);
            Route::get('/momo/add', [AutoPaymentController::class, 'addMomoView']);
            Route::post('/momo/add', [AutoPaymentController::class, 'addMomo']);
            Route::get('/vcb', [AutoPaymentController::class, 'vcb']);
            Route::get('/vcb/add', [AutoPaymentController::class, 'addVcbView']);
            Route::post('/vcb/add', [AutoPaymentController::class, 'addVcb']);
        });

        Route::prefix('/card-config')->group(function () {
            Route::get('/', [CardConfigController::class, 'index']);
            Route::get('/config', [CardConfigController::class, 'config']);
            Route::get('/active/{id}', [CardConfigController::class, 'active']);
            Route::post('/update-config', [CardConfigController::class, 'update']);
        });

        Route::prefix('/refund')->group(function () {
            Route::get('/', [RefundController::class, 'index']);
            Route::get('/refund-item/{id}', [RefundController::class, 'refund']);
            Route::get('/delete/{id}', [RefundController::class, 'delete']);
            Route::post('/multi-refund', [RefundController::class, 'multiRefund']);
        });
        Route::prefix('/package')->group(function () {
            Route::get('/', [PackageController::class, 'index']);
            Route::get('/detail/{id}', [PackageController::class, 'detail']);
            Route::post('/update', [PackageController::class, 'update']);
            Route::get('/prices/{id}', [PackageController::class, 'prices']);
            Route::get('/menu/{id}', [PackageController::class, 'package']);
            Route::post('/prices/update', [PackageController::class, 'updatePrices']);
        });
        Route::prefix('/menu')->group(function () {
            Route::get('/', [MenuController::class, 'index']);
            Route::get('/{id}', [MenuController::class, 'detail']);
            Route::post('/update', [MenuController::class, 'update']);
        });

        Route::prefix('/notify')->group(function () {
            Route::get('/', [NotifyController::class, 'index']);
            Route::get('/add', [NotifyController::class, 'addView']);
            Route::get('/delete/{id}', [NotifyController::class, 'remove']);
            Route::post('/add', [NotifyController::class, 'add']);
        });

        Route::prefix('/promotion')->group(function () {
            Route::get('/', [PromotionController::class, 'index']);
            Route::post('/update', [PromotionController::class, 'update']);
        });

        Route::prefix('/config')->group(function () {
            Route::get('/', [ConfigController::class, 'index']);
            Route::post('/update', [ConfigController::class, 'update']);
            Route::post('/update-token', [ConfigController::class, 'updateToken']);
        });

        Route::prefix('/payment')->group(function () {
            Route::get('/', [PaymentConfigController::class, 'index']);
            Route::get('/add', [PaymentConfigController::class, 'addView']);
            Route::post('/add', [PaymentConfigController::class, 'add']);
            Route::get('/remove/{id}', [PaymentConfigController::class, 'remove']);
//            Route::post('/update', [PaymentConfigController::class, 'update']);
        });
    });
});

//Route::get('/order-to-gach-the/{type}', function ($type) {
//
//    if ($type == 'facebook') {
//        $array = [
//            'facebook_comment_sv3',
//            'facebook_comment_sv4',
//            'facebook_comment_sv5',
//            'facebook_comment_sv6',
//            'facebook_comment_sv7',
//            'facebook_comment_sv8'
//        ];
//        $data = App\Models\Ads\Facebook\Facebook::whereIn('package_name', $array)->where('id', '>', 123065)->orderBy('id', 'desc')->paginate(30)->items();
//        return response()->json(['data' => $data]);
//    } elseif ($type == 'instagram') {
//        $data = Instagram::where('id', '>', 1245)->whereIn('package_name', ['instagram_like_sv2', 'instagram_follow_sv2'])->orderBy('id', 'desc')->take(100)->get();
//        return response()->json(['data' => $data]);
//    }
//    return response()->json(['data' => []]);
//});
