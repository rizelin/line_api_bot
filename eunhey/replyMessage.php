<?php
ini_set("display_errors", 1);
require_once("./mysql.php");
/*Reply Message
유저 > 라인플랫폼 >1.webhook으로 전송받은 것을 json형식으로 변경해 받기> 서버
    <            <2.json으로 받은 값을 수집,판단 후 3.응답을 cURL로 보냄<
판단에 따라 반환메세지 다르게
텍스트,사진,영상,소리,파일,위치정보,스탬프&템플릿
*/
$access_token = "R9zF7p4DyNms6QD3rE1fNAsatTOBqE79ggYsrD1pkPGi7TRnC0Re7ea1wdZHqZcF6GBtNvq4XgyOdPvx3MPIt3CHX6FonuxeAqOMIuIsuV/ATHUamfe071jBKd9IIQTfw+fG4ixGzubPbgcvXZYETQdB04t89/1O/w1cDnyilFU=";
//cURL할 주소
$url = 'https://api.line.me/v2/bot/message/reply';
$headers = array('Content-Type: application/json'
                ,'Authorization: Bearer ' . $access_token);

//1.line webhook로부터 json데이터를 받는다.
$raw = file_get_contents('php://input');  //유저가 보낸 정보
$receive = json_decode($raw, true);       //json형식으로 변경

//2.받은 이벤트를 분석,꺼내 변수로 저장
$event = $receive['events'][0];

$userType = $event['source']['type']; //유저 타입
$userId = $event['source']['userId'];
$replyToken  = $event['replyToken']; //유저토큰

$msgType = $event['type'];
$message_text = $event['message']['text']; // 유저가 대답한 워드

//유저가 보낸 텍스트내용에 따라 메세지타입 다르게/*바디 메세지의 들어갈 형식으로 적기
switch ($message_text) {
  case '1': $message = array('type' => 'text', 'text' => 'はい、テキストです。');
    break;
  case '2': $message = array('type' => 'image'
                         , 'originalContentUrl' => 'https://www.farnnie.com/pic-labo/peach2018-2.jpg'
                         , 'previewImageUrl' => 'https://www.farnnie.com/pic-labo/peach2018-2.jpg'
                          );
    break;
  case '3': $message = array('type' => 'video'
                           , 'originalContentUrl' => 'https://tv.naver.com/v/8423227'
                           , 'previewImageUrl' => 'https://www.nintendo.co.jp/character/mario/top/img/top/img-chara-06_02-low.png'
                          );

    break;
  case '4': $message = array(   'type' => 'location'
                              , 'title' => 'my location'
                              , 'address' => '〒150-0002 東京都渋谷区渋谷２丁目２１−１'
                              , 'latitude' => '35.65910807942215'
                              , 'longitude' => '139.70372892916203'
                            );
    break;
  case '5': $message = array('type'      => 'sticker'
                           , 'packageId' => '11537'
                           , 'stickerId' => '52002738');
    break;
  case '6': $template = array('type'    => 'confirm'
                            , 'text'    => 'いま暇？'
                            , 'actions' => array(
                                                array('type' => 'message', 'label' => 'うん', 'text' => '暇だよー')
                                               ,array('type' => 'message', 'label' => 'いや', 'text' => '忙しい')
                                                )
                            );
            $message =  array('type'    => 'template'
                            , 'altText' => '代替テキスト'
                            , 'template'=> $template
                            );
    break;
  default: $message = array('type' => 'text', 'text' => '他の数字のを送ってください。' );
    break;
}

//DBにユーザー情報保存
if ($event['type'] == 'follow') {

  $sql = "SELECT * FROM `line_user` WHERE `user_id`= '$userId'";
  if ($res = $_link->query($sql)) {
     if ($res->num_rows == 0) {
         $coumns = "`type`,`user_id`";
         $values = "'{$userType}','{$userId}'";
         if (isset($event['source']['groupId'])){$groupId=$event['source']['groupId']; $coumns.="`group_id`"; $values.= "'$groupId'";}
         elseif (isset($event['source']['roomId'])){$roomId=$event['source']['roomId']; $coumns.="`room_id`"; $values.= "'$roomId'";}

         $sql = "INSERT INTO `line_user`($coumns) VALUES ($values)";
         if ($res = $_link->query($sql)) {
              $message = "友達追加してくれてありがとう";
         }
     }else {
         $message="hi";
     }
  }
}
$msg = array('type' => 'text', 'text' => $message);

//요청바디를 작성
  $body = json_encode(array('replyToken' => $replyToken
                          , 'messages'   => array($msg)));


// 3.post json with curl 원하는 주소의 페이지에서 내가 임의의 값을 넣고 그 넣은 값으로 페이지에서 리턴되는 값을 받아오는 역할
// 서버-> 라인플랫폼으로 HTTPS접속
// CURLOPT_POSTFIELDS     : HTTP "POST"로 보내는 모든 데이터. 파일을 전송하려면 파일 이름 앞에 @를 붙여 전체 경로를 지정합니다.
//                          파일 유형을 명시 적으로 지정하려면 파일 이름 뒤에 '; type = mimetype'형태로 계속합니다.
//                          이 매개 변수는 'para1 = val1 & para2 = val2 & ...'처럼 url 인코딩 된 문자열 형식으로 전달할 수 있으며,
//                          필드 이름을 키 데이터를 값으로하는 배열로 전달할 수 있습니다. value가 배열의 경우, Content-Type 헤더는 multipart / form-data를 설정합니다.
$options = array(CURLOPT_URL            => $url
                ,CURLOPT_CUSTOMREQUEST  => 'POST'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_HTTPHEADER     => $headers
                ,CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl); //실행
curl_close($curl);//닫음
