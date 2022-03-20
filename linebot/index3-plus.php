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
$headers = array('Content-Type: application/json',
                'Authorization: Bearer ' . $access_token);

if ($event['message']['text']=="1") {
    // 입력 받은 내용 보내기
    $message = array('type' => 'text'
                    ,'text' => $message_text);
}else if($event['message']['text']=="2"){
    // img 보내기
    $message = array('type' => 'image'
                    ,'originalContentUrl' => "https://www.u-presscenter.jp/assets_c/2017/10/12510-thumb-462x160-31702.jpg"
                    ,'previewImageUrl' => "https://www.farnnie.com/pic-labo/peach2018-2.jpg");
}else if($event['message']['text']=="3"){
    // Video 보내기
    $message = array('type' => 'video'
                    ,'originalContentUrl' => "https://r3---sn-npoe7ney.googlevideo.com/videoplayback?id=o-ALGpChrkL-vKHXMqvz4vU1JGwFAoi95LW74yjGxkysE0&itag=18&source=youtube&requiressl=yes&pl=15&ei=6ajrXLj2Oo7Y8gT4rZnACA&mime=video%2Fmp4&gir=yes&clen=4037151&ratebypass=yes&dur=58.282&lmt=1411927910326684&fvip=3&c=WEB&ip=158.69.116.65&ipbits=0&expire=1558969674&sparams=clen,dur,ei,expire,gir,id,ip,ipbits,itag,lmt,mime,mip,mm,mn,ms,mv,pl,ratebypass,requiressl,source&signature=6771A04B6BC4EBB6F84F71BA9434F85EC8A539EE.6CF05730372EF940951B5951DACDB8CA914010F8&key=cms1&redirect_counter=1&cm2rm=sn-p5qe7l7s&req_id=e1c21afd64ffa3ee&cms_redirect=yes&mip=36.2.154.54&mm=34&mn=sn-npoe7ney&ms=ltu&mt=1558947988&mv=m"
                    ,'previewImageUrl' => "https://www.nintendo.co.jp/character/mario/top/img/top/img-chara-06_02-low.png");

}else if($event['message']['text']=="4"){
    // Yes/No 선택 탬플릿
    $template = array('type'    => 'confirm',
                      'text'    => 'テキストメッセージ。最大240文字',
                      'actions' => array(
                                     array('type'=>'message', 'label'=>'yes', 'text'=>'yesを押しました' ),
                                     array('type'=>'message', 'label'=>'no',  'text'=>'noを押しました' )
                                    )
                    );
    $message = array('type'     => 'template',
                     'altText'  => '代替テキスト',
                     'template' => $template
                    );
}else if($event['message']['text']=="5"){
    //복수 사진 템플렛 + 설명
    $columns = array(
                    array('thumbnailImageUrl' => 'https://blog-imgs-120.fc2.com/c/o/l/colorfilter/fc2blog_201803050111345bc.jpg',
                          'title'   =>  'タイトル最大40文字',
                          'text'    =>  'タイトルか画像がある場合は最大60文字、どちらもない場合は最大120文字',
                          'actions' =>  array(array('type' => 'message', 'label' => 'ラベルです', 'text' => 'メッセージ'))
                      ),
                    array('thumbnailImageUrl' => 'https://rurubu.jp/img_srw/andmore/images/DqCYWh7Cr0iHw5J2kht67Fd6Cbdnl7oUv5Wk6J9o.jpeg',
                          'title'   => 'タイトル最大40文字',
                          'text'    => 'タイトルか画像がある場合は最大60文字、どちらもない場合は最大10文字',
                          'actions' => array(array('type' => 'message', 'label' => 'ラベルです', 'text'  => 'メッセージ'))
                      )
                  );

    $template = array('type'    => 'carousel',
                       'columns' => $columns
                   );

    $message = array('type'     => 'template',
                     'altText'  => '代替テキスト',
                     'template' => $template
                 );
}else if($event['message']['text']=="6"){
    //복수 사진 템플렛
    $columns = array(
                    array('imageUrl' => 'https://blog-imgs-120.fc2.com/c/o/l/colorfilter/fc2blog_201803050111345bc.jpg',
                          'action' =>  array('type' => 'postback', 'label' => 'ラベルです', 'data' => 'action=buy&itemid=111')
                      ),
                    array('imageUrl' => 'https://rurubu.jp/img_srw/andmore/images/DqCYWh7Cr0iHw5J2kht67Fd6Cbdnl7oUv5Wk6J9o.jpeg',
                          'action' => array('type' => 'message', 'label' => 'ラベルです', 'text'  => 'メッセージ')
                      )
                  );

    $template = array('type'    => 'image_carousel',
                       'columns' => $columns
                   );

    $message = array('type'     => 'template',
                     'altText'  => '代替テキスト',
                     'template' => $template
                 );
}else if($event['message']['text']=="7"){
    // 보낼 내용에 유저 ID
    $message_text = $event['source']['userId'];
    $message = array('type' => 'text'
                    ,'text' => $message_text);
}else if($event['message']['text']=="8"){
    // 스티커
    $message = array('type' => 'sticker'
                    ,'packageId' => 11537
                    ,'stickerId' => 52002734);
}else if($event['message']['text']=="9"){
    //오디오
    $message = array('type' => 'audio'
                    ,'originalContentUrl' => "https://r3---sn-npoe7ney.googlevideo.com/videoplayback?id=o-ALGpChrkL-vKHXMqvz4vU1JGwFAoi95LW74yjGxkysE0&itag=140&source=youtube&requiressl=yes&pl=15&ei=6ajrXLj2Oo7Y8gT4rZnACA&mime=audio%2Fmp4&gir=yes&clen=936295&dur=58.281&lmt=1411927933036898&fvip=3&keepalive=yes&c=WEB&ip=158.69.116.65&ipbits=0&expire=1558969674&sparams=clen,dur,ei,expire,gir,id,ip,ipbits,itag,lmt,mime,mip,mm,mn,ms,mv,pl,requiressl,source&signature=63C399EBF6E2D0A244D81D35E9D4DEE37390E308.06B638B3D8966A22D9CC1A787A3B98F8CB264887&key=cms1&ratebypass=yes&redirect_counter=1&cm2rm=sn-p5qe7l7s&req_id=534e43d306fda3ee&cms_redirect=yes&mip=36.2.154.54&mm=34&mn=sn-npoe7ney&ms=ltu&mt=1558948127&mv=m"
                    ,'duration' => 60000);

}else{
    $message = array('type' => 'text'
                    ,'text' => $message_text);
}

// build request body
// 썸네일 previewImageUrl
// 내용 originalContentUrl


$body = json_encode(array('replyToken' => $reply_token
                        ,'messages'   => array($message)));


// post json with curl
$options = array(CURLOPT_URL            => $url,
                 CURLOPT_CUSTOMREQUEST  => 'POST',
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_HTTPHEADER     => $headers,
                 CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl);
curl_close($curl);
