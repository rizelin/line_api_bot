<?php
/*Reply Template Message
buttom,확인,카루세루,사진카루세루 형식으로 유저에게 답장보내기
*/
$accessToken = "R9zF7p4DyNms6QD3rE1fNAsatTOBqE79ggYsrD1pkPGi7TRnC0Re7ea1wdZHqZcF6GBtNvq4XgyOdPvx3MPIt3CHX6FonuxeAqOMIuIsuV/ATHUamfe071jBKd9IIQTfw+fG4ixGzubPbgcvXZYETQdB04t89/1O/w1cDnyilFU=";
$url = "https://api.line.me/v2/bot/message/reply";

//from user
$raw = file_get_contents('php://input');
$receive = json_decode($raw,true);

$event = $receive['events'][0];
$replyToken = $event['replyToken'];
$messageText = $event['message']['text'];

//button,confirm,
switch ($messageText) {
  case '1': $template = array('type' => 'buttons'
                             ,'thumbnailImageUrl' => 'https://d13n9ry8xcpemi.cloudfront.net/photo/odai/400/09ea43327c9291ffc05271c147ec5dbb_400.jpg'
                             ,'imageAspectRatio' => 'rectangle'
                             ,'imageSize' => 'cover'
                             ,'imageBackgroundColor' => '#FFFFFF'
                             ,'title' => 'ｶﾜ(・∀・)ｲｲ!!'
                             ,'text' => '犬です'
                             ,'defaultAction' => array('type'=>'uri','label'=>'View detail','uri'=>'https://blog-imgs-120.fc2.com/c/o/l/colorfilter/fc2blog_201803050111345bc.jpg' )
                             ,'actions' => array(array('type'=>'postback','label'=>'本当！','displayText'=>'かわいいでしょ','data'=>'action=buy&itemid=123')
                                                ,array('type'=>'message','label'=>'そうかな？','text'=>'まあ、そうね！' )
                                                ,array('type'=>'uri','label'=>'猫が好き！','uri'=>'http://ehimeinuneko.chu.jp/dogcat/?cat=12')
                                                ,array('type'=>'datetimepicker','label'=>'見に行く','data'=>'storeId=12','mode'=>'datetime','initial'=>'2019-05-26t00:00','max'=>'2030-01-01t00:00','min'=>'1900-01-01t00:00')
                                              ));
    break;
  case '2':$template = array('type' => 'confirm'
                             ,'text' => 'Are you sure?'
                             ,'actions' => array(
                                             array('type' => 'message','label'=>'こんにちは！','text'=>'(誰だろ、、)' ),
                                             array('type' => 'message','label'=>'誰ですか？？','text'=>'始めますが、' )
                              ));
    break;
  case '3':$template = array('type' => 'carousel'
                            ,'columns' => array(
                                  array('thumbnailImageUrl' => 'https://odl.abc-cooking.co.jp/srv/recipe/park/images/recipe-title-main-2864.jpg'
                                      , 'imageBackgroundColor	' => '#FFFFFF'
                                      , 'title' => 'プディング'
                                      , 'text' => '食べたいもの選んでね'
                                      , 'defaultAction' => array('type'=>'uri','label'=>'これ！','uri'=>'https://www.abc-cooking.co.jp/plus/recipe/detail/?id=2864' )
                                      , 'actions' => array(array('type' => 'message','label'=>'いいね','text'=>'><'),
                                                           array('type' => 'message','label'=>'べつに','text'=>'そか')
                                                          )
                                    ),
                                  array('thumbnailImageUrl' => 'https://www.yutori.co.jp/recipe/images/726_big.jpg'
                                      , 'imageBackgroundColor	' => '#FFFFFF'
                                      , 'title' => 'ブラウニー'
                                      , 'text' => '食べたいもの選んでね'
                                      , 'defaultAction' => array('type'=>'message','label'=>'detail','text'=>'これ？下のボタンをタッチしてね')
                                      , 'actions' => array(array('type'=>'uri','label'=>'レシピ見に行く','uri'=>'https://www.yutori.co.jp/recipe/?_action=detail&recipe_id=726'),
                                                           array('type'=>'postback','label'=>'美味しそう','displayText'=>'食べたいなー','data'=>'action=buy')
                                                          )
                                    ),

                             )
                             ,'imageAspectRatio' => 'rectangle'
                             ,'imageSize' => 'cover'
                           );
    break;
  case '4':$template = array('type' => 'image_carousel'
                             ,'columns' => array(array('imageUrl' => 'https://www.yutori.co.jp/recipe/images/726_big.jpg'
                                                      ,'action' =>array('type'=>'postback','label'=>'hi','displayText'=>'食べたいなー','data'=>'action=hi&id=11')),
                                                 array('imageUrl' => 'https://d13n9ry8xcpemi.cloudfront.net/photo/odai/400/09ea43327c9291ffc05271c147ec5dbb_400.jpg'
                                                      ,'action' =>array('type'=>'message','label'=>'yes','text'=>'yes')),
                                                 array('imageUrl' => 'https://odl.abc-cooking.co.jp/srv/recipe/park/images/recipe-title-main-2864.jpg'
                                                      ,'action' =>array('type'=>'uri','label'=>'hi','uri'=>'https://www.yutori.co.jp/recipe/?_action=detail&recipe_id=726'))
                                )
                             );
    break;
  default:
    break;
}

$message = array('type'     => 'template'
                ,'altText'  => 'こんにちは'
                ,'template' => $template);


$header = array('Content-Type: application/json',
                'Authorization: Bearer '.$accessToken );

$body = json_encode(array('replyToken' => $replyToken
                         ,'messages'  => array($message)));


$options = array(CURLOPT_URL            => $url
                ,CURLOPT_CUSTOMREQUEST  => 'POST'
                ,CURLOPT_RETURNTRANSFER => true
                ,CURLOPT_HTTPHEADER     => $header
                ,CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();
curl_setopt_array($curl,$options);
curl_exec($curl);
curl_close($curl);
?>
