<?php

require_once("./require/mysql2.php");
$accessToken = 'L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=';
/*
  1.친구 추가 할 시 DB접속하여 유저정보를 저장, 유저에게 인사메세지 보내기
  2.유저 프로필 정보도 가져와 저장
  3.유저가 보내는 메세지들을 저장
  4.출퇴근,휴식 디비저장
*/
//cURL할 주소
$url = 'https://api.line.me/v2/bot/message/reply';
//line webhook로부터 json데이터를 받는다.
$raw = file_get_contents('php://input');  //유저가 보낸 정보
$receive = json_decode($raw, true);       //json형식으로 변경

$headers = array('Content-Type: application/json'
                ,'Authorization: Bearer ' . $accessToken);

//받은 이벤트를 분석,꺼내 변수로 저장
$event = $receive['events'][0];
$replyToken  = $event['replyToken']; //유저토큰
$userType = $event['source']['type']; //유저 타입
$userId = $event['source']['userId'];

$message['message_id'] = $event['message']['id'];
$message['message_type'] = $event['message']['type']; //text,image,video,audio,sticker,location
$userTalk = $event['message']['text'];


$time = date("Y/m/d");
switch ($userTalk) {
  case '1':$template = array('type' => 'confirm'
                             ,'text' => $time.'出勤しますか？'
                             ,'actions' => array(
                                             array('type'=>'postback','label'=>'出勤','displayText'=>'出勤します','data'=>'1'),
                                             array('type'=>'message','label'=>'いいえ','text'=>'いいえ')
                              ));
    break;
  case '2':$template = array('type' => 'confirm'
                               ,'text' => $time.'退勤しますか？'
                               ,'actions' => array(
                                               array('type'=>'postback','label'=>'退勤','displayText'=>'退勤します','data'=>'2'),
                                               array('type'=>'message','label'=>'いいえ','text'=>'いいえ')
                                ));
    break;
  case '3':$template = array('type' => 'confirm'
                               ,'text' => $time.'休憩始めますか？'
                               ,'actions' => array(
                                               array('type'=>'postback','label'=>'休始','displayText'=>'休始します','data'=>'3'),
                                               array('type'=>'message','label'=>'いいえ','text'=>'いいえ')
                                ));
    break;
    case '4':$template = array('type' => 'confirm'
                                 ,'text' => $time.'休憩終わりますか？'
                                 ,'actions' => array(
                                                 array('type'=>'postback','label'=>'休終','displayText'=>'休終します','data'=>'4'),
                                                 array('type'=>'message','label'=>'いいえ','text'=>'いいえ')
                                  ));
      break;

  default:
    // code...
    break;
}
$message = array('type'     => 'template'
                ,'altText'  => 'こんにちは'
                ,'template' => $template);

if ($event['type'] == 'postback') {
    $data = $event['postback']['data'];
    $sqlFlg =FALSE;
    $date = date("Y-m-d");
    //insert attendance DB
    $sql = "SELECT `id` FROM `line_user` WHERE `user_id`='$userId'"; //유저의 프라이머리 아이디
    if ($res = $_link->query($sql)) {
      while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
          $employeeId = $row;
      }
      $employeeId = implode("",$employeeId);
    }

    //insert rest_time DB //유저의 퇴근안찍은 가장 늦은 아이디값
    $sql = "SELECT MAX(`id`) FROM `attendance` WHERE `employee_id`='{$employeeId}'";
            if ($res = $_link->query($sql)) {
              while($row = $res->fetch_array(MYSQLI_ASSOC)) {
                  $attendanceId = $row;
              }
              $attendanceId = implode("",$attendanceId);
            }


//punch_in
    switch ($data) {
      case '1': $cnt = 1;
                //당일출근확인(가장마지막 칼럼이 오늘날짜인지? 오늘날짜칼럼이 없으면 출근처리/있다면 중복OR2번쨰 출근인지 확인)
                $sql = "SELECT `punch_in`,`punch_out` FROM `attendance` WHERE date(`punch_in`) = '{$date}' AND `employee_id`='{$employeeId}'";
                if ($res = $_link->query($sql)) {
                  while($row = $res->fetch_array(MYSQLI_ASSOC)) {
                    $result=$row;
                    $cnt++;
                  }
                    if (empty($result)) { //1.出勤処理
                        $result ="出勤";
                        $sql = "INSERT INTO `attendance`(`employee_id`,`punch_in`) VALUES('{$employeeId}',NOW())";
                    }else {
                      //다시한번 물어보는 버튼를 보여줌
                          if ($result['punch_out'] == '0000-00-00 00:00:00') {
                            $result = $result['punch_in']."にもう出勤処理がされていますが、もう一回出勤処理しますか？"; //2.출근중복
                          }else {
                            $result = $date."の".$cnt."番目の出勤しますか？";  //3.당일복수출근
                          }
                    }
                }
        break;
      case '2': $msg ="退勤";
                //만약 퇴근을 까먹고 안찍었다면???......마지막 출근날짜가 오늘일자가 아니라면 날짜보여주고 이날의 퇴근이 맞습니까?
                //가장 마지막칼럼의 출퇴근비교 마지막칼럼의 퇴근값이 없음(퇴근처리) 있음(중복)
                $sql = "SELECT `punch_in`,`punch_out` FROM `attendance` WHERE `employee_id`='{$employeeId}' AND `id`='{$attendanceId}'";
                if ($res = $_link->query($sql)) {
                  while($row = $res->fetch_array(MYSQLI_ASSOC)) {
                      $result = $row;
                  }
                  $punchIn = date("Y-m-d",strtotime($result['punch_in']));
                  if ($result['punch_out'] == '0000-00-00 00:00:00') {
                      if ($punchIn == $date) { //1.퇴근처리
                          $result="退勤";
                          $sql = "UPDATE `attendance` SET `punch_out`=NOW() WHERE `employee_id`='{$employeeId}' AND `id`= '{$attendanceId}' AND `punch_out`='0000-00-00 00:00:00'";
                      }else { //5.퇴근 안찍었던 날이나 익일퇴근
                          $result = $punchIn."の退勤をしますか？";
                      }
                  }else {//날짜확인으로 출근안찍었거나 처리가 된거거나
                    if ($punchIn == $date) {
                        $result = "もう退勤処理がされています。"; //2.퇴근중복
                    }else {
                        $result = "今日の出勤情報がありません。"; //3.당일자 출근안찍음
                    }
                  }
                }
        break;
      case '3': $msg ="休始";
                $sql = "SELECT `punch_out` FROM `attendance` WHERE `id`='{$attendanceId}'";
                if ($res = $_link->query($sql)) {
                    $punchOut ="hey";
                    while($row = $res->fetch_array(MYSQLI_ASSOC)) {
                        $punchOut = $row;
                    }
                }
                $sql = "INSERT INTO `rest_time`(`attendance_id`,`start_time`) VALUES('{$attendanceId}',NOW())";
        break;
      case '4': $msg ="休終";
                //가장 마지막 아이디칼럼이 퇴근하지 않았고 개시만 있을떄(휴식끝이 널)
                $sql = "SELECT MAX(`id`) FROM `rest_time` WHERE `attendance_id`='{$attendanceId}'";
                  if ($res = $_link->query($sql)) {
                    while($row = $res->fetch_array(MYSQLI_ASSOC)) {
                        $restTimeId = $row;
                    }
                  }
                $sql = "UPDATE `rest_time` SET `end_time`=NOW() WHERE `id`='{$restTimeId}' AND `end_time` IS NULL";
        break;
      default:
        break;
    }

    if ($res = $_link->query($sql)) {
        if ($_link->affected_rows == 1) {
          $sqlFlg = TRUE;
        }else {
        }
    }
    $message = array('type' => 'text', 'text' => $result);  //요청바디를 작성
}



$body = json_encode(array('replyToken' => $replyToken
                        , 'messages'   => array($message)));

$options = array(CURLOPT_URL            => $url
                ,CURLOPT_CUSTOMREQUEST  => 'POST'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_HTTPHEADER     => $headers
                ,CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl); //실행
curl_close($curl);//닫음

//1.DBにユーザー情報保存
if ($event['type'] == 'follow') {
  // require_once("./addUser.php");
}

//ユーザー名前確認して更新
// require_once("./profile.php");

?>
