<?php
$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";

// receive json data from line webhook
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);

// parse received events
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];

$msg_id = $event['message']['id'];


// 정보 취득부분  정보취득할려면 콘텐트 id가 있어야함
// $url = "https://api.line.me/v2/bot/message/9936622990417/content";
// $url = "https://api.line.me/v2/bot/message/9948138284880/content";
//음성
// $url = "https://api.line.me/v2/bot/message/9958754195100/content";
//비디오
// $url = "https://api.line.me/v2/bot/message/9958890092232/content";



$headers = array('Authorization: Bearer ' . $access_token);
$body = "";
$options = array(CURLOPT_URL            => $url
                ,CURLOPT_CUSTOMREQUEST  => 'GET'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_HTTPHEADER     => $headers
                ,CURLOPT_POSTFIELDS     => $body);

//curl_세션선언
$curl = curl_init();
//curl 세션쎄팅
curl_setopt_array($curl, $options);
//curl 세션실행
$res = curl_exec($curl);
//curl 클린/사용종료
curl_close($curl);

// echo $res;
// $res0 = base64_encode($res);
// echo $res0;

// header('Content-Type: image/jpg');
header('Content-Type: audio/mpeg');
echo $res;

// header('Content-Length: ' . filesize($res));
// header('Cotent-Disposition: attachment; filename=goo.gif');
// readfile($res);

// echo $res0;
// $kumo = file_get_contents("nadeko.jpg");
// $res0 = base64_decode($res0);





// build request headers
$headers = array('Content-Type: application/json; charset=utf-8'
                ,'Authorization: Bearer ' . $access_token);

// 앵무새 챗봇
// 유저 워드를 보낼 말에 그대로 넣기
$message = array('type' => 'text'
                ,'text' => "Hi");

// build request body
$body = json_encode(array('to' => 'Ue6824041a4f68a7ef205494623659b08'
                        ,'messages'   => array($message)));


// post json with curl
// CURLOPT_URL           ：취득하는 URL입니다. curl_init () 세션을 초기화 할 때 지정할 수 있습니다.
// CURLOPT_CUSTOMREQUEST ：HTTP 요청에서 "GET"또는 "HEAD"이외에 사용하는 사용자 정의 메소드.
//                      　 이것이 유용한 것은 "DELETE"및 기타 잘 알려지지 않은 HTTP 요청을 실행하는 경우입니다.
//                      　 사용 가능한 값은 "GET", "POST", "CONNECT"등입니다.
// CURLOPT_RETURNTRANSFER : TRUE를 설정하면 curl_exec ()의 반환 값을 문자열로 반환합니다. 일반적으로 데이터를 직접 출력합니다.
// CURLOPT_HTTPHEADER     : 설정 HTTP 헤더 필드의 배열. array ( 'Content-type : text / plain', 'Content-length : 100') 형식.
// CURLOPT_POSTFIELDS     : HTTP "POST"로 보내는 모든 데이터. 파일을 전송하려면 파일 이름 앞에 @를 붙여 전체 경로를 지정합니다.
//                          파일 유형을 명시 적으로 지정하려면 파일 이름 뒤에 '; type = mimetype'형태로 계속합니다.
//                          이 매개 변수는 'para1 = val1 & para2 = val2 & ...'처럼 url 인코딩 된 문자열 형식으로 전달할 수 있으며,
//                          필드 이름을 키 데이터를 값으로하는 배열로 전달할 수 있습니다. value가 배열의 경우, Content-Type 헤더는 multipart / form-data를 설정합니다.
$options = array(
                CURLOPT_CUSTOMREQUEST  => 'POST'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_BINARYTRANSFER => true
                ,CURLOPT_HEADER         => true
                ,CURLOPT_HTTPHEADER     => $headers
                ,CURLOPT_POSTFIELDS     => $body);

$curl = curl_init('https://api.line.me/v2/bot/message/push');
curl_setopt_array($curl, $options);

//実行
$result = curl_exec($curl);

$error = curl_errno($curl);
if($error){
    return;
}

// echo $res;
?>
