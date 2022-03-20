<?php

$headers = array('Content-Type: application/json',
                 'Authorization: Bearer ' . $access_token):

$message = array('type' => 'text',
                 'text' => 'こんにちは登録のため、名前だけ入力してください。
                            例：山田 太郎');

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
