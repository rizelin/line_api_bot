<?php

// 유저가 대답한 워드를 수집
$message_text = $event['message']['text'];

if($message_text){

}

$headers = array('Content-Type: application/json',
                 'Authorization: Bearer ' . $access_token):


$template = array('type'   => 'confirm',
                  'text'   => 'お名前は「'.$message_text.'」で合ってますか？',
                  'actions' => array(
                                     array('type'=>'postback', 'label'=>'はい合ってます', 'data'=>'' ),
                                     array('type'=>'postback', 'label'=>'いいえ違います',  'data'=>'もう一度お名前を記入ください' )
                                    )
                  )

$body = json_encode(array('replyToken' => $reply_token,
                          'messages'   => array($message)));

$optimize = array(CURLOPT_URL            => $url,
                  CURLOPT_CUSTOMREQUEST  => 'POST',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_HTTPHEADER     => $headers,
                  CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl);
curl_close($curl);

?>
