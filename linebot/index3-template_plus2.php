<?php

$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";

$url = 'https://api.line.me/v2/bot/message/reply';

// receive json data from line webhook
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);


// parse received events
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];
// // 유저가 대답한 워드를 수집
// $message_text = $event['message']['text'];


// build request headers
$headers = array('Content-Type: application/json'
                ,'Authorization: Bearer ' . $access_token);

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


// build request body
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
