<?php
/*Push Message :userid필요
 서버 >준비한 값을 cURL로 전송> 라인플랫폼-유저
*/
$accessToken = "R9zF7p4DyNms6QD3rE1fNAsatTOBqE79ggYsrD1pkPGi7TRnC0Re7ea1wdZHqZcF6GBtNvq4XgyOdPvx3MPIt3CHX6FonuxeAqOMIuIsuV/ATHUamfe071jBKd9IIQTfw+fG4ixGzubPbgcvXZYETQdB04t89/1O/w1cDnyilFU=";
//$userId = "U11b744d0356fcb1ad53e30b74f8f79fe";
$url = "https://api.line.me/v2/bot/message/push";


$header = array('Content-Type: application/json'
                ,'Authorization: Bearer '.$accessToken);


$template = array('type' => 'buttons'
                            ,'thumbnailImageUrl' => 'https://d13n9ry8xcpemi.cloudfront.net/photo/odai/400/09ea43327c9291ffc05271c147ec5dbb_400.jpg'
                            ,'imageAspectRatio' => 'rectangle'
                            ,'imageSize' => 'cover'
                            ,'imageBackgroundColor' => '#FFFFFF'
                            ,'title' => '雇用区分'
                            ,'text' => 'あなたは？'
                           ,'defaultAction' => array('type'=>'uri','label'=>'View detail','uri'=>'https://blog-imgs-120.fc2.com/c/o/l/colorfilter/fc2blog_201803050111345bc.jpg' )
                            ,'actions' => array(array('type'=>'postback','label'=>'正社員','displayText'=>'正社員です','data'=>'employment_type=1')
                                              ,array('type'=>'postback','label'=>'アルバイト','displayText'=>'バイトです','data'=>'employment_type=2')
                                              ,array('type'=>'datetimepicker','label'=>'見に行く','data'=>'storeId=12','mode'=>'datetime','initial'=>'2019-05-26t00:00','max'=>'2030-01-01t00:00','min'=>'1900-01-01t00:00')
                                            ));

//$sendMsg ="金曜日だよ！！";
$message = array('type'     => 'template'
                ,'altText'  => 'こんにちは'
                ,'template' => $template);

//$massage = array('type' => 'text','text' => $sendMsg);

$body = json_encode(
          array(
                 'to' => $userId
                ,'messages' => array($massage)
                )
        );


//준비된 값을 cURL로 라인플랫폼으로 보냄
$options = array( CURLOPT_URL => $url
                 ,CURLOPT_CUSTOMREQUEST  => 'POST'
                 ,CURLOPT_BINARYTRANSFER => true
                 ,CURLOPT_BINARYTRANSFER => true //true면 CURLOPT_BINARYTRANSFER이 사용된 경우에 출력결과를 가공하지 않고 반환
                 ,CURLOPT_HEADER         => true //true면 헤더내용도 출력
                 ,CURLOPT_HTTPHEADER     => $header
                 ,CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();     //세션초기화
curl_setopt_array($curl, $options);
curl_exec($curl);        //세션실행
curl_close($curl);       //세션닫기


?>
