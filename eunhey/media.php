<?php
$accessToken = "R9zF7p4DyNms6QD3rE1fNAsatTOBqE79ggYsrD1pkPGi7TRnC0Re7ea1wdZHqZcF6GBtNvq4XgyOdPvx3MPIt3CHX6FonuxeAqOMIuIsuV/ATHUamfe071jBKd9IIQTfw+fG4ixGzubPbgcvXZYETQdB04t89/1O/w1cDnyilFU=";

function media($msgId){
$requestURL = "https://api.line.me/v2/bot/message/{$msgId}/content";
$headers = array('Authorization: Bearer '.$accessToken);

$options = array(CURLOPT_URL            => $requestURL
                ,CURLOPT_CUSTOMREQUEST  => 'GET'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_HTTPHEADER     => $headers
                );

$curl = curl_init(); //세션초기화
curl_setopt_array($curl, $options);
$data = curl_exec($curl); //세션실행 후, json 문자열을 배열로 저장
curl_close($curl); //세션닫음
$message = base64_encode($data);
//바이너리 형식으로 저장 base64..뭐시꺵이로 확인할 순있음. get.php파일 확인하기

//$message = dechex(bindec($date));
//$message = preg_match('/^[\x61-\x7A\x41-\x5A\x30-\x39]+$/',$message);
//$message = hex2bin($date);

/*
//画像ファイルの作成
$fileInfo = "testfile.txt";
$fp = fopen($fileInfo, "wb");

if ($fp){
  $message = "bye";
      if (flock($fp, LOCK_EX)){
        if (fwrite($fp,$message) === FALSE){
              $message= 'ファイル書き込みに失敗しました';
        }else{
              $message= $data.'をファイルに書き込みました';
        }

        flock($fp, LOCK_UN);
    }else{
        $message= 'ファイルロックに失敗しました';
    }
}else {
  $message="ファイル生成失敗";
}
fclose($fp);
*/

return $message;
}

/*
binary(2진수)-->16진수-->아스
-bin2hex()  2진수 > 16진수
-bindec()   2진수 > 10진수
-decbin()   10진수 > 2진수
-dechex()   10진수 > 16진수
-hexdec()   16진수 > 10진수

string type
decbin(hexdec($date))
dechex(bindec($date))

$data = pack("C*",$data);
$arr = unpack("C*",$data);
$message = var_dump($arr);
foreach ($arr as $key => $value) {
     $message = "\$arr[$key] = $value\n";
}

$data = pack("C*",23,17,208);
$arr = unpack("C*",$data);
foreach ($arr as $key => $value) {
     echo "\$arr[$key] = $value\n";
}
*/
?>
