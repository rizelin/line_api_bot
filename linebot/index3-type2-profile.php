<?php

$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";


$userId = "U50f308edd3263f9ebd2a65fccafa9d28";

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

echo $res['userId'];
