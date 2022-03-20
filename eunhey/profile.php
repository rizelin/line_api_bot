<?php
/*Profile getProfile($event['source']['userId'])
서버 >취득할 profile의 유저ID를 get으로 넣어 cURL통신> 라인플랫폼
            < json형식으로 profile값 받음<
*/
$accessToken = "R9zF7p4DyNms6QD3rE1fNAsatTOBqE79ggYsrD1pkPGi7TRnC0Re7ea1wdZHqZcF6GBtNvq4XgyOdPvx3MPIt3CHX6FonuxeAqOMIuIsuV/ATHUamfe071jBKd9IIQTfw+fG4ixGzubPbgcvXZYETQdB04t89/1O/w1cDnyilFU=";

//cURL할 주소
$url = "https://api.line.me/v2/bot/profile/{$userId}";

// build request headers 요청헤더를 작성
$headers = array('Authorization: Bearer ' .$accessToken);

// post json with curl 원하는 주소의 페이지에 값을 넣고 그 넣은 값으로 페이지에서 리턴되는 값을 받아오는 역할
// CURLOPT_URL           ：취득하는 URL입니다. curl_init () 세션을 초기화 할 때 지정할 수 있습니다.
// CURLOPT_CUSTOMREQUEST ：HTTP 요청에서 "GET"또는 "HEAD"이외에 사용하는 사용자 정의 메소드.
//                      　 이것이 유용한 것은 "DELETE"및 기타 잘 알려지지 않은 HTTP 요청을 실행하는 경우입니다.
//                      　 사용 가능한 값은 "GET", "POST", "CONNECT"등입니다.
// CURLOPT_RETURNTRANSFER : TRUE를 설정하면 curl_exec ()의 반환 값을 문자열로 반환. 일반적으로 데이터를 직접 출력합니다.
// CURLOPT_HTTPHEADER     : 설정 HTTP 헤더 필드의 배열. array ( 'Content-type : text / plain', 'Content-length : 100') 형식.
// CURLOPT_POSTFIELDS     : HTTP "POST"로 보내는 모든 데이터. 파일을 전송하려면 파일 이름 앞에 @를 붙여 전체 경로를 지정합니다.
//                          파일 유형을 명시 적으로 지정하려면 파일 이름 뒤에 '; type = mimetype'형태로 계속합니다.
//                          이 매개 변수는 'para1 = val1 & para2 = val2 & ...'처럼 url 인코딩 된 문자열 형식으로 전달할 수 있으며,
//                          필드 이름을 키 데이터를 값으로하는 배열로 전달할 수 있습니다. value가 배열의 경우, Content-Type 헤더는 multipart / form-data를 설정합니다.
$options = array(CURLOPT_URL            => $url
                ,CURLOPT_CUSTOMREQUEST  => 'GET'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_HTTPHEADER     => $headers
                );

$curl = curl_init(); //세션초기화
curl_setopt_array($curl, $options);
$data = json_decode(curl_exec($curl),true); //세션실행 후, json 문자열을 배열로 저장
curl_close($curl); //세션닫음

$nickname = $data['displayName'];
$pictureUrl = $data['pictureUrl'];
//echo var_dump($data);

//유저이름저장 변경됐을시, 변경된 이름으로 저장
$sqlFlg = FALSE;
$sql = "SELECT `display_name` FROM `line_user` WHERE `user_id` ='{$userId}'";
if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
      $name = $row;
  }
  $sqlFlg = TRUE;
  if ($name != $nickname) {
    $sqlFlg = FALSE;
    $sql=(empty($name))? "INSERT INTO `line_user`(`display_name`) VALUES('{$nickname}')" : "UPDATE `line_user` SET `display_name`='{$nickname}' WHERE `user_id`='{$userId}'";
    if ($res = $_link->query($sql)) {
        if ($_link->affected_rows==1) {
            $sqlFlg = TRUE;
        }
    }
  }
}

if ($sqlFlg) {
  echo "SECCESS";
    $_link->commit();
}else {
  echo"FAIL";
    $_link->rollback();
}
