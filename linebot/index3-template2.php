<?php
//DB연결
require_once("../require/mysql.php");

$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";
$url = 'https://api.line.me/v2/bot/message/reply';

// receive json data from line webhook
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);


// parse received events
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];


if($event['type'] == 'follow'){
    require_once("./addUser.php");
    $follow = $event['type'];
}else if(isset($follow)){
    reqire_once("./addUser2.php");
}


// receive json data from line webhook
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);

// parse received events
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];
// $message_text = $event['message']['text'];

// build request headers
$headers = array('Content-Type: application/json'
                ,'Authorization: Bearer ' . $access_token);

// // Yes/No 선택을 하는 템플릿 Yes/No아니라도 상관없음 변경가능
// $template = array('type'    => 'confirm',
//                   'text'    => 'テキストメッセージ。最大240文字',
//                   'actions' => array(
//                                  array('type'=>'message', 'label'=>'yes', 'text'=>'yesを押しました' ),
//                                  array('type'=>'message', 'label'=>'no',  'text'=>'noを押しました' )
//                                 )
//                 );

if($event['message']['text'] = "勤怠"){
    // 근태관리
    $template = array('type'    => 'buttons',
        'text'    => '勤怠をお選びください',
        'actions' => array(
                        array('type'=>'message', 'label'=>'1', 'text'=>'1を押しました' ),
                        array('type'=>'message', 'label'=>'2',  'text'=>'2を押しました' ),
                        array('type'=>'message', 'label'=>'3',  'text'=>'3を押しました' ),
                        array('type'=>'message', 'label'=>'4',  'text'=>'4を押しました' ),
                    )
                );

}else if($event['message']['text'] = "デスク管理"){
    // 테스크관리
    $template = array('type'    => 'buttons',
        'text'    => 'デスク管理をお選びください',
        'actions' => array(
                        array('type'=>'message', 'label'=>'1', 'text'=>'1を押しました' ),
                        array('type'=>'message', 'label'=>'2',  'text'=>'2を押しました' ),
                        array('type'=>'message', 'label'=>'3',  'text'=>'3を押しました' ),
                        array('type'=>'message', 'label'=>'4',  'text'=>'4を押しました' ),
                    )
                );
}else {
    $message = array('type' => 'text'
                    ,'text' => $message_text);
}



$message = array('type'     => 'template',
                 'altText'  => '代替テキスト',
                 'template' => $template
                );

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
