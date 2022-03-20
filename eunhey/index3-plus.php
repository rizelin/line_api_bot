<?php
//서버측 ID
$access_token = "R9zF7p4DyNms6QD3rE1fNAsatTOBqE79ggYsrD1pkPGi7TRnC0Re7ea1wdZHqZcF6GBtNvq4XgyOdPvx3MPIt3CHX6FonuxeAqOMIuIsuV/ATHUamfe071jBKd9IIQTfw+fG4ixGzubPbgcvXZYETQdB04t89/1O/w1cDnyilFU=";
//사용할 서비스:메세지API
$url = 'https://api.line.me/v2/bot/message/reply';

// receive json data from line webhook 라인 웹훅으로 json데이터 주고받음
$raw = file_get_contents('php://input'); //유저가 보낸 정보
$receive = json_decode($raw, true); //jason으로

// parse received events 받은 정보 분석
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];
$message_text = $event['message']['text'];

// build request headers
$headers = array('Content-Type: application/json'
                ,'Authorization: Bearer ' . $access_token);

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
                    ,'originalContentUrl' => "https://tv.naver.com/v/8423227"
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
}else {
    $message = array('type' => 'text'
                    ,'text' => $message_text);
}

// build request body
// 썸네일 previewImageUrl
// 내용 originalContentUrl


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
