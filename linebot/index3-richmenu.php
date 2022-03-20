<?php

$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";
// 確認{"{\"richMenuId\":\"richmenu-2bc0a5766c1e0c6a0c191494d9330999\"}"
// open({画像のURL}) { |image|
// 	http_client = HTTPClient.new
// 	endpoint_uri = "https://api.line.me/v2/bot/richmenu/{3で取得したrichMenuId}/content"
// 	http_client.post_content(endpoint_uri, image.read,
// 		'Content-Type' => 'image/jpeg',
// 		'Authorization' => "Bearer {アクセストークン}"
// 	)
// }
// build request headers
// $headers = array(
//     'Content-Type: application/json',
//     'Authorization: Bearer ' . $access_token
// );
// // build request body
// $body = json_encode(
//     array(
//         'size'=>array(
//             'width'=>2500,
//             'height'=>1686
//         ),
//         'selected'=>true,
//         'name'=>'ワールドブレインズBOT',
//         'chatBarText'=>'ワールドブレインズBOT',
//         // 'areas'=>
//         //     array(
//         //         'bounds'=>array(
//         //             'x'=>0,
//         //             'y'=>0,
//         //             'width'=>2500,
//         //             'height'=>1686
//         //         ),
//         //         'action'=>array(
//         //             'type'=>'postback',
//         //             'data'=>'1'
//         //         )
//         //     )
//         'areas'=> array(
//     		array(
//         		'bounds'=> array(
//         		'x'=> 0,
//         		'y'=> 0,
//         		'width'=> 1250,
//         		'height'=> 843
//         		),'action'=> array(
//         			'type'=> 'postback',
//         			'data'=> '1'
//         		)
//     		)
//     	)
//     )
// );
// // post json with curl
// $options = array(
//     CURLOPT_URL=>'https://api.line.me/v2/bot/richmenu',
//     CURLOPT_CUSTOMREQUEST=>'POST',
//     CURLOPT_RETURNTRANSFER=>true,
//     CURLOPT_HTTPHEADER=>$headers,
//     CURLOPT_POSTFIELDS => $body
// );
// $curl = curl_init();
// curl_setopt_array($curl, $options);
// $res = curl_exec($curl);
// curl_close($curl);
$img = file_get_contents("2.jpg");
// $img = base64_encode($img);
$headers = array(
    'Authorization: Bearer ' . $access_token,
    'Content-Type: iamge/jped',
    'Content-Length: '.$img
);
// build request body
// $body = json_encode(
//     array(
//         'size'=>array(
//             'width'=>2500,
//             'height'=>1686
//         ),
//         'selected'=>false,
//         'name'=>'ワールドブレインズBOT',
//         'chatBarText'=>'ワールドブレインズBOT',
//         // 'areas'=>
//         //     array(
//         //         'bounds'=>array(
//         //             'x'=>0,
//         //             'y'=>0,
//         //             'width'=>2500,
//         //             'height'=>1686
//         //         ),
//         //         'action'=>array(
//         //             'type'=>'postback',
//         //             'data'=>'1'
//         //         )
//         //     )
//         'areas'=>
//             array(
//                 array(
//                     'bounds'=> array(
//                         'x'=> 0,'y'=> 0,'width'=> 2500,'height'=> 1686
//                     ),'action'=> array(
//                         'type'=> 'postback','data'=> '1'
//                     )
//                 )
//             )
//     )
// );
// post json with curl
$options = array(
    CURLOPT_URL=>'https://api.line.me/v2/bot/user/all/richmenu/richmenu-2bc0a5766c1e0c6a0c191494d9330999/content',
    CURLOPT_CUSTOMREQUEST=>'POST',
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_HTTPHEADER=>$headers,
    CURLOPT_POSTFIELDS => $body
);
$curl = curl_init();
curl_setopt_array($curl, $options);
$res = curl_exec($curl);
curl_close($curl);


$res1 = json_encode($res);

$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);
// parse received events
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];
$user_id = $event['source']['userId'];
// 유저가 대답한 워드를 수집
$message_text = '確認'.$res['richMenuId'].$res1;
// build request headers
$headers = array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
);
// 앵무새 챗봇
// 유저 워드를 보낼 말에 그대로 넣기
$message = array(
    'type' => 'text',
    'text' => $message_text
);
// build request body
$body = json_encode(
    array(
        'replyToken' => $reply_token,
        'messages'   => array($message)
    )
);
// post json with curl
$options = array(
    CURLOPT_URL=>'https://api.line.me/v2/bot/message/reply',
    CURLOPT_CUSTOMREQUEST=>'POST',
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_HTTPHEADER=>$headers,
    CURLOPT_POSTFIELDS => $body
);
$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl);
curl_close($curl);

?>

<!-- <!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title></title>
    </head>
    <body>
        <img src="1.jpg" alt="">
    </body>
</html> -->
