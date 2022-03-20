<?php
require_once("./require2/mysql.php");
// receive json data from line webhook
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);
// parse received events
$event = $receive['events'][0];
// $access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";
$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";

$event_type = $event['type'];
if($event_type == 'memberJoined'){
    $reply = $event['replyToken'];
}else{
    if(isset($event['replyToken'])){
        $reply = $event['replyToken'];
    }
    $type = $event['source']['type'];
    if($type == 'room'){
        $to = $event['source']['roomId'];
    }else if($type == 'group'){
        $to = $event['source']['groupId'];
    }else if($type == 'user'){
        $to = $event['source']['userId'];
    }else {
        $to = 'Cbd281d2761a78b8c4f75ede88e934b1e';
    }
}
if(isset($to)){
    // $user_id = $event['source']['userId'];
    // $sql = "SELECT`user_id`FROM`line_info`WHERE`user_id`='{$user_id}'";
    // if($res_sql = $_link->query($sql)){
    //     $list = array();
    //     while ($row = $res_sql->fetch_array(MYSQLI_ASSOC)) {
    //         $list[] = $row;
    //     }
    //     $count = count($list);
    // }
    // $test = $list[0]['user_id'];
    $test = json_encode($receive);
    $headers = array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Bearer ' . $access_token
    );
    $message = array(
        'type' => 'text',
        'text' => 'PUSH world：'.$to.'：'.$test
    );
    // build request body
    $body = json_encode(
        array(
            'to' => $to,
            'messages'   => array($message)
        )
    );
    $options = array(
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $body
    );
    $curl = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt_array($curl, $options);
    $result = curl_exec($curl);
    $error = curl_errno($curl);
    if($error){
        return;
    }
}
if(isset($reply)){
    $test = json_encode($receive);
    $headers = array('Content-Type: application/json; charset=utf-8'
                    ,'Authorization: Bearer ' . $access_token);
    $message = array('type' => 'text',
                     'text' => 'REPLY world'.$test);
    // build request body
    $body = json_encode(
        array(
            'replyToken' => $reply,
            'messages'   => array($message)
        )
    );
    $url = 'https://api.line.me/v2/bot/message/reply';
    $options = array(
        CURLOPT_URL             => $url,
        CURLOPT_CUSTOMREQUEST   => 'POST',
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_HTTPHEADER      => $headers,
        CURLOPT_POSTFIELDS      => $body
    );
    $curl = curl_init();
    curl_setopt_array($curl, $options);
    curl_exec($curl);
    curl_close($curl);
}
