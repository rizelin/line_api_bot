<?php
// 은혜 DB연결
require_once("./require2/mysql.php");

$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";

$url = 'https://api.line.me/v2/bot/message/reply';

// receive json data from line webhook
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);

// parse received events
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];
// 유저가 대답한 워드를 수집
$message_text = $event['message']['text'];

//매번 사용하는 변수들을 함수로 변경
function line_bot_reply($url,$reply_token,$message){

    $headers = array('Content-Type: application/json',
                     'Authorization: Bearer ' . 'L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=');

    $body = json_encode(
                        array(
                               'replyToken' => $reply_token,
                               'messages'   => array($message))
                       );
    $options = array(
                     CURLOPT_URL => $url,
                     CURLOPT_CUSTOMREQUEST  => POST,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_HTTPHEADER     => $headers,
                     CURLOPT_POSTFIELDS     => $body);
     //curl_세션선언
     $curl = curl_init();
     //curl_세션쎄팅
     curl_setopt_array($curl, $options);
     //curl_세션실행
     curl_exec($curl);
     //curl_세션종료
     curl_close($curl);
};
function line_bot_push($message,$user_id){
    $headers = array('Content-Type: application/json; charset=utf-8',
                     'Authorization: Bearer ' . 'L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=');

    // $message = array('type' => 'text',
    //                  'text' => 'hello world2');
    $body = json_encode(array('to' => $user_id,
                              'messages'   => array($message)));
    $options = array(
                     CURLOPT_CUSTOMREQUEST  => 'POST',
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_BINARYTRANSFER => true,
                     CURLOPT_HEADER         => true,
                     CURLOPT_HTTPHEADER     => $headers,
                     CURLOPT_POSTFIELDS     => $body);

    $curl = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt_array($curl, $options);
    $result = curl_exec($curl);
    $error = curl_errno($curl);
}


$sql = "SELECT `user_id`,`step`
        FROM   `line_profile_step`";
if($res = $_link->query($sql)){
    $list = array();
    while($row = $res->fetch_array(MYSQLI_ASSOC)){
        $list[] = $row;
    }
    $count = count($list);
}

// var_dump($list);

//DB에서 현재 진행중인지 확인한다
//여러명 동시라도 처리 가능하게 만드는중
for ($i=0; $i < $count; $i++) {
    //만약 아이디가 있다면 스탭별로 넘어간다
    if($list[$i]['user_id'] == $event['source']['userId']){
        $user_id = $list[$i]['user_id'];
        $step = $list[$i]['step'];
        break;
    }else{
        $step = 0;
    }
}


//현재는 숫자1이긴 하나 추후에 팔오우 상태일때로 바꿀 예정
//시작할때 팔로우시 시작하는 코드문
if($message_text == 1 || $step == 0){
    // for ($i=0; $i < 10; $i++) {
        $message = array('type' => 'text',
                         'text' => 'こんにちは登録のため、名前だけ入力してください。例：山田 太郎'
                        );
        line_bot_reply($url,$reply_token,$message);
        // }

    //추후에 이 조건을 위에 임시 조건과 변경함
    // + 팔로우를 걸었을 시
    if($step == 0){
        $user_id = "'".$_link->real_escape_string($event['source']['userId'])."'";
        $sql = "INSERT INTO `line_profile_step` (`user_id`,`step`)VALUES ({$user_id},1)";
        $res = $_link->query($sql);
    }
}

// $test = json_encode($event);

if(isset($event['postback']['data'])){
    $postback = explode("/",$event['postback']['data']);
}
//스텝1 맞는지 아닌지 판별한다
if($step == 1 && isset($postback[0])){
    if($postback[0] == YES && $postback[1] == 1 && $postback[2] == $user_id){
        $message = array('type' => 'text',
                         'text' => "登録しました");
        line_bot_reply($url,$reply_token,$message);
        // DB에 아직 안넣은상태 나중에 쿼리문 짜서 넣어야함

        //회사를 묻는다
        $sql = "SELECT `company_name`
        FROM   `company`";
        if($res = $_link->query($sql)){
            $list = array();
            while($row = $res->fetch_array(MYSQLI_ASSOC)){
                $list[] = $row;
            }
                $count = count($list);
        }

        $j = 0;
        for ($i=0; $i < $count; $i++) {
            if (!$i == 0 && $i%3 == 0) {
              $j = $j+1;
            }
              $companys[$j][$i] = array('type' => 'message', 'label' => $list[$i]['company_name'], 'text' => $list[$i]['company_name']);
        }
        unset($j);

        $count_division = $count%3;
        if($count_division == 1){
          $count_division = 2;
        }else if($count_division == 2){
          $count_division = 1;
        }
        $companys = array();
        $count_a = $count+$count_division;
        $j = 0;
        for ($i=0; $i < $count_a; $i++) {
            if (!$i == 0 && $i%3 == 0) {
                     $j = $j+1;
            }
            if ($i < $count) {
              $companys[$j][$i] = array('type' => 'postback', 'label' => $list[$i]['company_name'], 'data' => "YES/2/");
            }else{
              $companys[$j][$i] = array('type' => 'postback', 'label' => '空きテキスト', 'data' => "NO/2/".$user_id);
            }
        }
        unset($j);

        $columns_num = $count_a/3;
        for ($i=0; $i < $columns_num; $i++) {
             $k=0;
             for ($j=0; $j < $count_a; $j++) {
                  if (isset($companys[$i][$j])) {
                      $k = $k+1;
                      $companys[$i][$k-1] = $companys[$i][$j];
                  }
             }
        }
        for ($i=0; $i < $columns_num; $i++) {
            for ($j=3; $j < $count_a; $j++) {
                unset($companys[$i][$j]);
            }
        }
        $columns = array();
        $company = array();
        for ($i=0; $i < $columns_num; $i++) {
                $columns[$i] = array('type'    => 'buttons',
                                     'text'    => '会社名をお選びください',
                                     'actions' => $company[$i] = $companys[$i]
                                    );
        }
        $template = array('type'    => 'carousel',
                          'columns' => $columns
                         );

        $message = array('type'     => 'template',
                         'altText'  => '代替テキスト',
                         'template' => $template
                        );
        line_bot_push($message,$user_id);

    }else if($postback[0] == NO && $postback[2] == $user_id){
        $message = array('type' => 'text',
                         'text' => "お名前をもう一度入力お願いします");
        line_bot_reply($url,$reply_token,$message);
    }
}else if ($step == 1) {
// $message_text
// '포스트데이터'.$event['postback']['data'].
    //'type'    => 'confirm' 가로로 2개만 템플릿 버튼 만듦
    $template = array('type'    => 'confirm',
                      'text'    => '「'.$message_text.$event['postback']['data'].'」でよろしいでしょうか？',
                      'actions' => array(
                                        array(
                                              'type' => 'postback',
                                              'label' => 'はい',
                                              'data' => "YES/1/".$user_id."/".$message_text
                                             ),
                                        array(
                                              'type' => 'postback',
                                              'label' => 'いいえ',
                                              'data' => "NO/1/".$user_id
                                             )
                                       )
                    );

    $message = array('type'    => 'template',
                     'altText' => 'モバイルデバイスで確認できます',
                     'template' => $template
                    );
    line_bot_reply($url,$reply_token,$message);
//스탭2로 진행한다
}
