<?php

$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";

$url = 'https://api.line.me/v2/bot/message/reply';

// receive json data from line webhook
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);

// parse received events
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];
$message_text = $event['message']['text'];


// build request headers
$headers = array('Content-Type: application/json'
                ,'Authorization: Bearer ' . $access_token);

//비디오 영상을 보내는 문구
//動画ファイルのURL（最大文字数：1000）HTTPS mp4 最大長：1分 最大ファイルサイズ：10MB
$message = array('type' => 'video'
                ,'originalContentUrl' => "https://r3---sn-npoe7ney.googlevideo.com/videoplayback?id=o-ALGpChrkL-vKHXMqvz4vU1JGwFAoi95LW74yjGxkysE0&itag=18&source=youtube&requiressl=yes&pl=15&ei=6ajrXLj2Oo7Y8gT4rZnACA&mime=video%2Fmp4&gir=yes&clen=4037151&ratebypass=yes&dur=58.282&lmt=1411927910326684&fvip=3&c=WEB&ip=158.69.116.65&ipbits=0&expire=1558969674&sparams=clen,dur,ei,expire,gir,id,ip,ipbits,itag,lmt,mime,mip,mm,mn,ms,mv,pl,ratebypass,requiressl,source&signature=6771A04B6BC4EBB6F84F71BA9434F85EC8A539EE.6CF05730372EF940951B5951DACDB8CA914010F8&key=cms1&redirect_counter=1&cm2rm=sn-p5qe7l7s&req_id=e1c21afd64ffa3ee&cms_redirect=yes&mip=36.2.154.54&mm=34&mn=sn-npoe7ney&ms=ltu&mt=1558947988&mv=m"
                ,'previewImageUrl' => "https://www.nintendo.co.jp/character/mario/top/img/top/img-chara-06_02-low.png");

$body = json_encode(array('replyToken' => $reply_token
                        ,'messages'   => array($message)));


// post json with curl
$options = array(CURLOPT_URL            => $url
                ,CURLOPT_CUSTOMREQUEST  => 'POST'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_HTTPHEADER     => $headers
                ,CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl);
curl_close($curl);
