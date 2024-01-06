<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Lib;
use App\Service\_1FbService\_1FbService;
use App\Service\AutoFbPro\AutoFbProService;
use App\Service\AutoLikeCC\AutoLikeCCService;
use App\Service\BaostarService\BaostarService;
use App\Service\BuffViewer\BuffViewerService;
use App\Service\Coin\CoinService;
use App\Service\CongLike\CongLikeService;
use App\Service\DichVuOnlineVnService\DichVuOnlineVnService;
use App\Service\DichVuSt\DichVuStService;
use App\Service\FarmApi\FarmService;
use App\Service\MFB\MfbService;
use App\Service\MlikeService\MLikeService;
use App\Service\MlikeV2Service\MlikeV2Service;
use App\Service\MuaViewVnService\MuaViewVnService;
use App\Service\Mxh2Service\Mxh2Service;
use App\Service\ProYtb\ProYtb;
use App\Service\SaBomMoService\SaBomMoService;
use App\Service\SbookService\SbookService;
use App\Service\Shop2Fa\Shop2FaService;
use App\Service\SubReVn\SubReVnService;
use App\Service\TangLikeCheo\TangLikeCheoService;
use App\Service\Tanglikegiare\TangLikeGiaReService;
use App\Service\TangLikeOrg\TangLikeOrgService;
use App\Service\Telegram\TelegramService;
use App\Service\ThanhLike\ThanhLikeService;
use App\Service\TraoDoiSub\TraoDoiSubService;
use App\Service\TrumLikeSub\TrumLikeSubService;
use App\Service\TrumSub\TrumSubService;
use App\Service\TrumVn\TrumVnService;
use App\Service\TuongTacCheoService\TuongTacCheoService;
use App\Service\VietNamFb\VietNamFbService;
use App\Service\ViewNhanh\ViewNhanhService;
use App\Service\ViewYT\ViewYTService;
use App\Service\VnKings\VnKingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Lib;
    protected $limit = 100;

    protected $mfbService;
    protected $autolikeccService;
    protected $tanglikecheoService;
    protected $coinSerivce;
    protected $sBookSerivce;
    protected $autoFbProService;
    protected $telegramService;
    protected $vnKingService;
    protected $buffViewerService;
    protected $tangLikeOrgService;
    protected $viewNhanhService;
    protected $trumLikeSub;

    protected $viewYTService;
    protected $vietNamFbService;
    protected $congLikeService;
    protected $trumSubService;
    protected $subReVn;
    protected $shop2FaService;
    protected $farmService;
    protected $proytb;
    protected $trumvn;
    protected $tanglikegiare;
    protected $_1fbService;
    protected $mlikeService;
    protected $thanhLike;
    protected $saBomMoService;
    protected $tuongTacCheoService;
    protected $traoDoiSubService;
    protected $mlikeV2Service;
    protected $baostarService;
    protected $dichVuOnlineService;
    protected $dichVuStService;
    protected $muaViewVnService;
    protected $mxh2Service;


    public function __construct(Request $request)
    {

//        parent::__construct($request);
//        parent::__construct();
        $this->limit = (!is_null($request->query("limit")) && (intval($request->query("limit")) < MAX_LIMIT)) ? $request->query("limit") : $this->limit;
        $this->method = $request->getMethod();
        if ($request->query('sort_by') && in_array(strtoupper($request->query('sort_by')), ['ASC', 'DESC'])) {
            $this->sort_by = strtoupper(trim(strip_tags($request->query('sort_by'))));
        }
        if ($request->query('order_by') && in_array(strtolower($request->query('order_by')), ['updated_at', 'created_at'])) {
            $this->order_by = strtolower(trim(strip_tags($request->query('order_by'))));
        }

        $this->mfbService = new MfbService();
        $this->autolikeccService = new AutoLikeCCService();
        $this->tanglikecheoService = new TangLikeCheoService();
        $this->coinSerivce = new CoinService();
        $this->sBookSerivce = new SbookService();
        $this->autoFbProService = new AutoFbProService();
        $this->telegramService = new TelegramService();
        $this->viewYTService = new ViewYTService();
        $this->buffViewerService = new BuffViewerService();
        $this->vnKingService = new VnKingService();
        $this->tangLikeOrgService = new TangLikeOrgService();
        $this->viewNhanhService = new ViewNhanhService();
        $this->trumLikeSub = new TrumLikeSubService();
        $this->vietNamFbService = new VietNamFbService();
        $this->congLikeService = new CongLikeService();
        $this->trumSubService = new TrumSubService();
        $this->subReVn = new SubReVnService();
        $this->shop2FaService = new Shop2FaService();
        $this->farmService = new FarmService();
        $this->proytb = new ProYtb();
        $this->trumvn = new TrumVnService();
        $this->tanglikegiare = new TangLikeGiaReService();
        $this->_1fbService = new _1FbService();
        $this->mlikeService = new MLikeService();
        $this->thanhLike = new ThanhLikeService();
        $this->saBomMoService = new SaBomMoService();
        $this->tuongTacCheoService = new TuongTacCheoService();
        $this->traoDoiSubService = new TraoDoiSubService();
        $this->mlikeV2Service = new MlikeV2Service();
        $this->baostarService = new BaostarService();
        $this->dichVuOnlineService = new DichVuOnlineVnService();
        $this->dichVuStService = new DichVuStService();
        $this->muaViewVnService = new MuaViewVnService();
        $this->mxh2Service = new Mxh2Service();
    }


    public function convertUID(Request $request)
    {
        $regex = "/facebook.com/";
        preg_match($regex, $request->get('link'), $data);
        //dd($data);
        if (count($data) < 1) {
            return $request->get('link');
        }
        $type = $request->get('type');
        if (is_numeric($request->get('link'))) {
            return $request->get('link');
        }
        if (in_array($type, ['follow', 'like_page'])) {
            $uid = $this->convertUidFacebook($request->get('link'));
            if ($uid) {
                return $uid;
            } else {
                return 0;
            }
        } elseif ($type == 'like_comment') {
            preg_match('/(comment_id=)([0-9]{10,})/', $request->get('link'), $link_id);
            if (isset($link_id[2])) {
                return $link_id[2];
            } else {
                return $request->get('link');
            }
        } else {

            $array = explode("/", getUrlReplaceString($request->get('link')));
            /*check video for type video*/
            if ($request->get('type') == 'video') {
                if (isset($array[3]) && $array[3] == 'reel') {
                    return $array[4] ?? $request->get('link');
                }
                if (!isset($array[4]) || (isset($array[4]) && $array[4] != 'videos')) {
                    return 0;
                }
            }
            /**/
            if (isset($array[3]) && $array[3] == 'watch') {
                if (isset($array[4])) {
                    return str_replace("?v=", "", $array[4]) ?? $request->get('link');
                } else {
                    return 0;
                }
            } elseif (isset($array[3]) && strpos($request->get('link'), "permalink.php")) {
                preg_match('/(.*)\/permalink.php\?story_fbid=(.*)(&)/', $request->get('link'), $data);
                return $data[2] ?? 0;
            } elseif (strpos($request->get('link'), "videos/pcb")) {
                return $array[6] ?? 0;

            } elseif (isset($array[3]) && $array[3] == 'reel') {
                return $array[4] ?? $request->get('link');
            } elseif (isset($array[5]) && strpos($request->get('link'), "photos")) {
                return $array[6] ?? $request->get('link');
            } elseif (isset($array[4]) && $array[4] == 'posts') {
                if (strrpos($array[5], ":")) {
                    $array[5] = strstr($array[5], ":", true);
                }//https://www.facebook.com/photo?fbid=2259354400898058&set=a.103741726459347
                if (strrpos($array[5], "?")) {
                    return strstr($array[5], "?", true) ?? $request->get('link');
                } else {
                    return $array[5] ?? $request->get('link');
                }
            } elseif (isset($array[4]) && $array[4] == 'videos') {
                return $array[5] ?? $request->get('link');
            } elseif (isset($array[5]) && $array[5] == 'permalink') {
                return $array[6] ?? $request->get('link');
            } else {
                $uid = $this->getPostIdFromFacebookUrl($request->get('link'), 'like');
                if ($uid == $request->get('link')) {
                    return 0;
                } else {
                    return $uid;
                }
            }
        }
    }

    public function convertMulti()
    {
        set_time_limit(60 * 60);
        $data = request()->get('data');
        $data = explode("\n", $data);
        foreach ($data as $item) {
            $uid = $this->convertUidFacebook($item);
            if ($uid) {
                echo $uid . "<br>";
            } else {
                return "<br>";;
            }
        }
    }

    public function curlTraoDoiSub($data)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://id.traodoisub.com/api.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'link=' . $data,
            CURLOPT_HTTPHEADER => array(
                'authority: id.traodoisub.com',
                'accept: application/json, text/javascript, */*; q=0.01',
                'x-requested-with: XMLHttpRequest',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'origin: https://id.traodoisub.com',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://id.traodoisub.com/',
                'accept-language: vi,en-US;q=0.9,en;q=0.8',
                'cookie: SL_G_WPT_TO=en; SL_GWPT_Show_Hide_tmp=1; SL_wptGlobTipTmp=1'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function convertUidFacebook($link)
    {
        if (strpos($link, "profile.php?id=")) {
            preg_match('/([0-9]{8,})/', $link, $checkProfile);
            if (isset($checkProfile[0])) {
                return $checkProfile[0];
            }
            return 0;
        }
        preg_match('/([0-9]{8,})/', $link, $checkProfile);
        if (false) {
            return $checkProfile[0];
        } else {

            $response_hvl = $this->convertHVL($link);
            if ($response_hvl) {
                return $response_hvl;
            }
            /*findid*/
            $link = $this->replace($link, 1);
            $data_link = explode("/", $link);

            if (isset($data_link[3])) {
                $response = $this->curlTraoDoiSub($link);
                if (isset($response->id)) {
                    return $response->id;
                }
            }

            if (isset($data_link[3])) {
                $username = $data_link[3];
                $data = ['username' => $username];
                $response = $this->curlFindId($data);
                if ($response && isset($response->data->id)) {
                    return $response->data->id;
                }
            }
            /*atp*/
            $dataApt = $this->curlToIdAtp($link);
            preg_match('/(>)(.*)(<\/textarea>)/', $dataApt, $checkProfile);
            if (isset($checkProfile[2])) {
                return $checkProfile[2];
            }
            $data_start_nhat = $this->curlToStartNhat($link);
            if (isset($data_start_nhat->uid)) {
                return $data_start_nhat->uid;
            }
            return false;
        }
    }

    public function convertHVL($link)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://thuycute.hoangvanlinh.vn/api/tool/get-uid-fb?link=' . $link,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        return $response->data->idfb ?? false;
    }

    public function curlToStartNhat($link)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://starnhat.vn/FacebookApi',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'type_api=Check_Username&userphone=0862125521&api_key=mC%2BUOOxYVgibCv8XP59GIo2OpNswrFCFi9DxddhQ8Ec%3D&username=' . $link,
            CURLOPT_HTTPHEADER => array(
                'authority: starnhat.vn',
                'sec-ch-ua: "Google Chrome";v="95", "Chromium";v="95", ";Not A Brand";v="99"',
                'accept: */*',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'x-requested-with: XMLHttpRequest',
                'sec-ch-ua-mobile: ?0',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36',
                'sec-ch-ua-platform: "Windows"',
                'origin: https://starnhat.vn',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://starnhat.vn/profile',
                'accept-language: en-US,en;q=0.9'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function replace($url, $only = false)
    {
        if ($only && strpos($url, "?") > -1) {
            return strstr($url, "?", true);
        }
        return $url;
    }

    public function curlFindId($data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.findids.net/api/get-uid-from-username',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'authority: api.findids.net',
                'sec-ch-ua: "Chromium";v="92", " Not A;Brand";v="99", "Google Chrome";v="92"',
                'accept: application/json, text/plain, */*',
                'sec-ch-ua-mobile: ?0',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36',
                'content-type: application/json;charset=UTF-8',
                'origin: https://findids.net',
                'sec-fetch-site: same-site',
                'sec-fetch-mode: cors',
                'sec-fetch-dest: empty',
                'referer: https://findids.net/',
                'accept-language: en-US,en;q=0.9'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function curlToIdAtp($link)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://id.atpsoftware.vn/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'linkCheckUid=' . $link,
            CURLOPT_HTTPHEADER => array(
                'authority: id.atpsoftware.vn',
                'cache-control: max-age=0',
                'sec-ch-ua: "Chromium";v="92", " Not A;Brand";v="99", "Google Chrome";v="92"',
                'sec-ch-ua-mobile: ?0',
                'upgrade-insecure-requests: 1',
                'origin: https://id.atpsoftware.vn',
                'content-type: application/x-www-form-urlencoded',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36',
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: navigate',
                'sec-fetch-user: ?1',
                'sec-fetch-dest: document',
                'referer: https://id.atpsoftware.vn/',
                'accept-language: en-US,en;q=0.9',
                'cookie: PHPSESSID=gf4t40sj7rvr31bbamt34rrjli; SL_GWPT_Show_Hide_tmp=1; SL_wptGlobTipTmp=1'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function callAutoFb($data, $url)
    {
        $key = '$w==fe6e95cf62387300ce50$TYy$j$4NzUxNg==';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60 * 6,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "api-token: " . $key,
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        return $response;
    }
}
