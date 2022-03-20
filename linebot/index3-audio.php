<?php

$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";

$url = 'https://api.line.me/v2/bot/message/reply';

// receive json data from line webhook
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);


// parse received events
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];


// build request headers
$headers = array('Content-Type: application/json'
                ,'Authorization: Bearer ' . $access_token);

// build request body
//音声ファイルのURL（最大文字数：1000）
//HTTPS m4a 最大長：1分 最大ファイルサイズ：10MB
$message = array('type' => 'audio'
                ,'originalContentUrl' => "https://r3---sn-npoe7ney.googlevideo.com/videoplayback?id=o-ALGpChrkL-vKHXMqvz4vU1JGwFAoi95LW74yjGxkysE0&itag=140&source=youtube&requiressl=yes&pl=15&ei=6ajrXLj2Oo7Y8gT4rZnACA&mime=audio%2Fmp4&gir=yes&clen=936295&dur=58.281&lmt=1411927933036898&fvip=3&keepalive=yes&c=WEB&ip=158.69.116.65&ipbits=0&expire=1558969674&sparams=clen,dur,ei,expire,gir,id,ip,ipbits,itag,lmt,mime,mip,mm,mn,ms,mv,pl,requiressl,source&signature=63C399EBF6E2D0A244D81D35E9D4DEE37390E308.06B638B3D8966A22D9CC1A787A3B98F8CB264887&key=cms1&ratebypass=yes&redirect_counter=1&cm2rm=sn-p5qe7l7s&req_id=534e43d306fda3ee&cms_redirect=yes&mip=36.2.154.54&mm=34&mn=sn-npoe7ney&ms=ltu&mt=1558948127&mv=m"
                ,'duration' => 60000);


// build request body
$body = json_encode(array('replyToken' => $reply_token
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
$options = array(CURLOPT_URL            => $url
                ,CURLOPT_CUSTOMREQUEST  => 'POST'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_HTTPHEADER     => $headers
                ,CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl);
curl_close($curl);
