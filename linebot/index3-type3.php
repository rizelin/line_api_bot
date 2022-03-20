<?php
$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";

$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);

// parse received events
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];

// $response = $bot->getProfile($event['source']['userId']);
// $response = get_profile($userId);
$userId = $event['source']['userId'];

// $userId = "U50f308edd3263f9ebd2a65fccafa9d28";
// 유저 정보를 구한다
$url = "https://api.line.me/v2/bot/profile/{$userId}";
$headers = array('Authorization: Bearer ' . $access_token);
$options = array(CURLOPT_URL            => $url
                ,CURLOPT_CUSTOMREQUEST  => 'GET'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_HTTPHEADER     => $headers);
$curl = curl_init();
curl_setopt_array($curl, $options);
$res = curl_exec($curl);
$res = json_decode($res,true);
curl_close($curl);



// 유저에게 대답할 내용을 꾸민다
if($event['source']['type'] == "group"){
    $type = "typeId : {$event['source']['groupId']}";
}else if($event['source']['type'] == "room"){
    $type = "typeId : {$event['source']['roomId']}";
}
// 유저가 대답한 워드를 수집
$message_text = "type : {$event['source']['type']}
{$type}
userId : {$event['source']['userId']}
displayName : {$res['displayName']}
pictureUrl : {$res['pictureUrl']}
statusMessage : {$res['statusMessage']}";

// build request headers
$headers = array('Content-Type: application/json'
                ,'Authorization: Bearer ' . $access_token);

// 앵무새 챗봇
// 유저 워드를 보낼 말에 그대로 넣기
$message = array('type' => 'text'
                ,'text' => $message_text);

//내용을 쎄팅한다
// build request body
$body = json_encode(array('replyToken' => $reply_token
                        ,'messages'   => array($message)));

$url = 'https://api.line.me/v2/bot/message/reply';

//내용을 보낸다
$options = array(CURLOPT_URL            => $url
                ,CURLOPT_CUSTOMREQUEST  => 'POST'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_HTTPHEADER     => $headers
                ,CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl);
curl_close($curl);
