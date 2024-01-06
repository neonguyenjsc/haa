<?php


namespace App\Service\Telegram;


use App\Http\Controllers\Traits\Lib;

class TelegramService
{

    use Lib;

    public function dataToText($data)
    {
        $txt = '';
        foreach ($data as $i => $item) {
            $txt = $txt . " $i : $item \n";
        }
        return $txt;
    }

    public function sendMessGroupFollowV2ToBotTelegram($data)
    {
        $curl = $this->curl('https://api.telegram.org/bot1473682903:AAELrkzxkEISPHNkOn85bGCvHHGe6ksbxFs/sendMessage?chat_id=-464181812&text=' . urlencode($this->dataToText($data)));
        return $curl;
    }

    public function sendMessGroupFollowV4_ToBotTelegram($data)
    {
        $curl = $this->curl('https://api.telegram.org/bot5417742397:AAF3-0UvL5Rwqad7gh8liYFJPpsCIgKaHdc/sendMessage?chat_id=-755727925&text=' . urlencode($this->dataToText($data)));
        return $curl;
    }

    public function sendMessGroupOrder1ToBotTelegram($data)
    {
        $curl = $this->curl('https://api.telegram.org/bot5417742397:AAF3-0UvL5Rwqad7gh8liYFJPpsCIgKaHdc/sendMessage?chat_id=-648291157&text=' . urlencode($this->dataToText($data)));
        return $curl;
    }

    public function sendMessGroupOrder2AllToBotTelegram($data)
    {
        $curl = $this->curl('https://api.telegram.org/bot5417742397:AAF3-0UvL5Rwqad7gh8liYFJPpsCIgKaHdc/sendMessage?chat_id=-694092804&text=' . urlencode($this->dataToText($data)));
        return $curl;
    }

    public function sendMessGroupOrder4AllToBotTelegram($mess)
    {
        $curl = $this->curl('https://api.telegram.org/bot5630418307:AAGcE_WzA7dOleJavAQQYP5xumMccuc6dXA/sendMessage?chat_id=-824294263&text=' . urlencode($mess));
        return $curl;
    }

    public function sendMessGroupOrderToBotTelegram($data)
    {
        $curl = $this->curl('https://api.telegram.org/bot1473682903:AAELrkzxkEISPHNkOn85bGCvHHGe6ksbxFs/sendMessage?chat_id=-450595769&text=' . urlencode($this->dataToText($data)));
//        $curl = $this->curl('https://api.telegram.org/bot1765291988:AAFrgdjPuye9HwNqOiuFDCjK5nNPC7DE-6A/sendMessage?chat_id=1072115940&text=' . urlencode($mess));
        return $curl;
    }

    public function sendMessGroupOrderAllToBotTelegram($data)
    {
        $curl = $this->curl('https://api.telegram.org/bot1473682903:AAELrkzxkEISPHNkOn85bGCvHHGe6ksbxFs/sendMessage?chat_id=-538252471&text=' . urlencode($this->dataToText($data)));
//        $curl = $this->curl('https://api.telegram.org/bot1765291988:AAFrgdjPuye9HwNqOiuFDCjK5nNPC7DE-6A/sendMessage?chat_id=1072115940&text=' . urlencode($mess));
        return $curl;
    }

    public function sendMessGroupOrderAllV3ToBotTelegram($data)
    {
        $curl = $this->curl('https://api.telegram.org/bot5417742397:AAF3-0UvL5Rwqad7gh8liYFJPpsCIgKaHdc/sendMessage?chat_id=-684924532&text=' . urlencode($this->dataToText($data)));
//        $curl = $this->curl('https://api.telegram.org/bot1765291988:AAFrgdjPuye9HwNqOiuFDCjK5nNPC7DE-6A/sendMessage?chat_id=1072115940&text=' . urlencode($mess));
        return $curl;
    }

    public function sendMessGroupOrderFollowToBotTelegram($data)
    {
        $curl = $this->curl('https://api.telegram.org/bot1473682903:AAELrkzxkEISPHNkOn85bGCvHHGe6ksbxFs/sendMessage?chat_id=-509884866&text=' . urlencode($this->dataToText($data)));
//        $curl = $this->curl('https://api.telegram.org/bot1765291988:AAFrgdjPuye9HwNqOiuFDCjK5nNPC7DE-6A/sendMessage?chat_id=1072115940&text=' . urlencode($mess));
        return $curl;
    }

    public function sendMessGroupOrderFillToBotTelegram($data)
    {
        $curl = $this->curl('https://api.telegram.org/bot1473682903:AAELrkzxkEISPHNkOn85bGCvHHGe6ksbxFs/sendMessage?chat_id=-595763045&text=' . urlencode($this->dataToText($data)));
//        $curl = $this->curl('https://api.telegram.org/bot1765291988:AAFrgdjPuye9HwNqOiuFDCjK5nNPC7DE-6A/sendMessage?chat_id=1072115940&text=' . urlencode($mess));
        return $curl;
    }

    public function sendMessGroupUpdateUserToBotTelegram($message)
    {
        $curl = $this->curl('https://api.telegram.org/bot1834611341:AAHkG58oBmyNsIugUBewTgA5mljrv88PSio/sendMessage?chat_id=-516118942&text=' . urlencode($message));
//        $curl = $this->curl('https://api.telegram.org/bot1765291988:AAFrgdjPuye9HwNqOiuFDCjK5nNPC7DE-6A/sendMessage?chat_id=1072115940&text=' . urlencode($mess));
        return $curl;
    }

    public function senToTelegramAutoPayment($mess)
    {
        $curl = $this->curl('https://api.telegram.org/bot1604398885:AAEki2LWrnbqRDz8Hvezb5K6E6eiuJPypJw/sendMessage?chat_id=-540281235&text=' . urlencode($mess));
        return $curl;
    }

    public function senToTelegramDDOS($mess)
    {
        $curl = $this->curl('https://api.telegram.org/bot914685080:AAEKWiw4x4M-ZWvNfX73_SRkbLG0LNULbqs/sendMessage?chat_id=-503835399&text=' . urlencode($mess));
        return $curl;
    }

    public function sendToTelegramDebug($data)
    {
        $mess = $this->dataToText($data);
        $curl = $this->curl('https://api.telegram.org/bot914685080:AAEKWiw4x4M-ZWvNfX73_SRkbLG0LNULbqs/sendMessage?chat_id=-532117746&text=' . urlencode($mess));
        return $curl;
    }

//    public function sendToTelegramDebugCheckOrder($data)
//    {
//        $mess = $this->dataToText($data);
//        $curl = $this->curl('https://api.telegram.org/bot1182802227:AAHqD9Kx_3Xue8VxVSrGZUh7jWYKtFvTuc0/sendMessage?chat_id=-607201117&text=' . urlencode($mess));
//        return $curl;
//    }
}
