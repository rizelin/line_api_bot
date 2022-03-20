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

//비디오 영상을 보내는 문구
//'originalContentUrl' 썸네일
//'previewImageUrl' 비디오 HTTPS MP4형식지원 최대1분 가능
$message = array('type' => 'video'
                ,'originalContentUrl' => "https://tv.naver.com/v/8423227"
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
