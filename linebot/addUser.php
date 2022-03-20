<?php

$headers = array('Content-Type: application/json'
                ,'Authorization: Bearer ' . $accessToken);

/*
  친구추가후, 취득,프로필정보 저장
  고용형태,부서,출근시간,퇴근시간 정보
*/
$sql = "SELECT * FROM `line_user` WHERE `user_id`= '$userId'";
if ($res = $_link->query($sql)) {
     if ($res->num_rows == 0) {
         $coumns = "`type`,`user_id`";
         $values = "'{$userType}','{$userId}'";
         if (isset($event['source']['groupId'])){$groupId=$event['source']['groupId']; $coumns.="`group_id`"; $values.= "'$groupId'";}
         elseif (isset($event['source']['roomId'])){$roomId=$event['source']['roomId']; $coumns.="`room_id`"; $values.= "'$roomId'";}

         $sql = "INSERT INTO `line_user`($coumns) VALUES ($values)";
         if ($res = $_link->query($sql)) {
              $message = "友達追加ありがとうございます。";
         }
     }
}
/*
$template = array('type' => 'buttons'
                          ,'thumbnailImageUrl' => 'https://d13n9ry8xcpemi.cloudfront.net/photo/odai/400/09ea43327c9291ffc05271c147ec5dbb_400.jpg'
                          ,'imageAspectRatio' => 'rectangle'
                          ,'imageSize' => 'cover'
                          ,'imageBackgroundColor' => '#FFFFFF'
                          ,'title' => '雇用区分'
                          ,'text' => 'あなたは？'
                          ,'defaultAction' => array('type'=>'uri','label'=>'View detail','uri'=>'https://blog-imgs-120.fc2.com/c/o/l/colorfilter/fc2blog_201803050111345bc.jpg' )
                          ,'actions' => array(array('type'=>'postback','label'=>'正社員','displayText'=>'正社員です','data'=>'roll=1&employment_type=1')
                                             ,array('type'=>'postback','label'=>'バイト','displayText'=>'バイトです','data'=>'roll=1&employment_type=2' )
                                             ,array('type'=>'uri','label'=>'猫が好き！','uri'=>'http://ehimeinuneko.chu.jp/dogcat/?cat=12')
                                             ,array('type'=>'datetimepicker','label'=>'見に行く','data'=>'storeId=12','mode'=>'datetime','initial'=>'2019-05-26t00:00','max'=>'2030-01-01t00:00','min'=>'1900-01-01t00:00')
                                           ));

 $message = array('type'     => 'template'
                 ,'altText'  => 'こんにちは'
                 ,'template' => $template);*/
$message = array('type' => 'text', 'text' => $message);  //요청바디를 작성
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
//require_once("pushMessage.php");
?>
