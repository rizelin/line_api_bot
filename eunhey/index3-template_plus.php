<?php

$access_token = "R9zF7p4DyNms6QD3rE1fNAsatTOBqE79ggYsrD1pkPGi7TRnC0Re7ea1wdZHqZcF6GBtNvq4XgyOdPvx3MPIt3CHX6FonuxeAqOMIuIsuV/ATHUamfe071jBKd9IIQTfw+fG4ixGzubPbgcvXZYETQdB04t89/1O/w1cDnyilFU=";

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

//다중 이미지 템블렛 전송
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
