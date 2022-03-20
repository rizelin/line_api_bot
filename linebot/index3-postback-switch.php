<?php
// 은혜 DB연결
require_once("./require2/mysql.php");

//오직 하나의 종류에서 이용하면 변수로 할 것 없이 그대로 하는것도 가능
//하지만 여러 부분에서 사용하게 되었으므로 변수로서 엑세스 코드를 사용한다
//**추가 현재 2개부분에서 사용하므로 변수로서 쓰고 기존 함수안에 있는 엑세스코드를 지운다.
$access_token = "L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=";

//리플레이 URL
$url = 'https://api.line.me/v2/bot/message/reply';

//사용자가 보낸 값을 변수에 대입한다
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);

//이벤트에 필요한 정보를 대입한다
$event = $receive['events'][0];
$reply_token  = $event['replyToken'];
// 유저가 대답한 워드를 수집
$message_text = $event['message']['text'];

//매번 사용하는 변수들을 함수로 변경
// 리플레이
function line_bot_reply($access_token,$url,$reply_token,$message){

    //헤드에 있는 엑세스 코드 변수로 변경하기!!
    $headers = array('Content-Type: application/json',
                     'Authorization: Bearer ' . $access_token);
    //리플레이 토큰과 응답할 대답을 대입
    $body = json_encode(
                        array(
                               'replyToken' => $reply_token,
                               'messages'   => array($message))
                       );
    //사용 URL코드들
    $options = array(
                     CURLOPT_URL => $url,
                     CURLOPT_CUSTOMREQUEST  => POST,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_HTTPHEADER     => $headers,
                     CURLOPT_POSTFIELDS     => $body);
     //curl_세션선언
     $curl = curl_init();
     //curl_세션쎄팅
     curl_setopt_array($curl, $options);
     //curl_세션실행
     curl_exec($curl);
     //curl_세션종료
     curl_close($curl);
}
// 푸쉬 메세지 보내기용
function line_bot_push($access_token,$message,$user_id){

    //헤드에 있는 엑세스 토큰 변수로 변경하기
    $headers = array('Content-Type: application/json; charset=utf-8',
                     'Authorization: Bearer ' . $access_token);

    //to의 정보에 보낼 상대의 유저 아이디를 대입한다
    $body = json_encode(array('to' => $user_id,
                              'messages'   => array($message)));
    //URL의 정보를 대입한다
    $options = array(
                     CURLOPT_CUSTOMREQUEST  => 'POST',
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_BINARYTRANSFER => true,
                     CURLOPT_HEADER         => true,
                     CURLOPT_HTTPHEADER     => $headers,
                     CURLOPT_POSTFIELDS     => $body);
    //curl에 푸쉬 url를 설정
    $curl = curl_init('https://api.line.me/v2/bot/message/push');
    //쎄팅
    curl_setopt_array($curl, $options);
    //실행
    $result = curl_exec($curl);
    //에러가 없을시 실행?? 지금 확인 전부안됨 추후에 다시 확인 할 것
    $error = curl_errno($curl);
}
// 복수 선택용 탬플릿
function line_bot_tamplate($user_id,$count,$list,$select_text,$select_column,$select_id,$yes,$no,$null_text){

    //리스트 값이 3의 배열이 안될시에 그 값을 채워서 맞춰준다
    //$count원래 값  $count_division 원래값을3으로 나눈값 if분기로 값이3이 되도록 조정
    //$count_a = $count+$count_division; 최종값이 3의 배수가 되도록 조정
    $count_division = $count%3;
    if($count_division == 1){
      $count_division = 2;
    }else if($count_division == 2){
      $count_division = 1;
    }
    //복수 탬플랫의 경우 한 하나당 선택지 3개만 만들어지므로 나누는 작업을 실시한다
    //$companys[$j(1개당)][$i(3개들어감)] 3%를 사용하여 3의 배수를 계산하여 넣는 작업
    // $j 시작은 0번째로 시작하여 3의 배수일때 카운트가 1씩 증가한다
    $companys = array();
    $count_a = $count+$count_division;
    $j = 0;
    for ($i=0; $i < $count_a; $i++) {
        //0번째는 걸러낸다 3의 배수를 걸러낸다
        if (!$i == 0 && $i%3 == 0) {
                 $j = $j+1;
        }
        //회사&부서&고용형태 선택 리스트[$i]번째 안에 값을 찾는다
        //기존 데이터를 대입
        if ($i < $count) {
            $companys[$j][$i] = array('type' => 'postback', 'label' => $yes.$list[$i][$select_column], 'data' => $yes.$user_id.'/'.$list[$i][$select_column].'/'.$list[$i][$select_id]);
            //3의 배수로 안맞아떨어질때 더미 데이터 대입
        }else{
            $companys[$j][$i] = array('type' => 'postback', 'label' => $no.'空きテキスト', 'data' => $no.$user_id.'/'.$null_text);
        }
    }
    //사용한 값 초기화
    unset($j);

    //$companys배열 값에서 템플릿 출력시에
    //배열값이 0,1,2 값만 받으므로 $companys[1][$j(3)]3이상의 값이 있을경우
    //인식이 안되서 출력이 안되므로 제거작업
    //2중 포문을 사용한다 첫 번째 포문은 큰 배열이므로 1/3나누어서 사용
    //$companys[$i(탬플랫1개당)][$k(버튼3개)] *탬플릿당 버튼3개가 아니면 안돌아감
    //이 작업으로 0 1 2 번째에 4 5 6 이상의 값들이 대입을 한다
    //탬플릿은 버튼의 1/3이므로 나누어서 필요한 값 만큼 넣는다.

    $columns_num = $count_a/3;
    for ($i=0; $i < $columns_num; $i++) {
        //0부터 시작할 초기값 설정
         $k=0;
         for ($j=0; $j < $count_a; $j++) {
             //4 5 6배열이상의 값들을 0 1 2배열에 대입한다
              if (isset($companys[$i][$j])) {
                  //$k의 값이 1 2 3 이므로 대입할때 -1하여 0 1 2로 대입한다
                  $k = $k+1;
                  $companys[$i][$k-1] = $companys[$i][$j];
              }
         }
    }
    //작업 후에는 4 5 6 이상의 값을을 언셋으로 지워준다
    //안지우면 탬플릿 인식안함-다중 탬플릿사용 기준 0 1 2 번만 사용하며 배열이 3개(고정)인식함
    for ($i=0; $i < $columns_num; $i++) {
        //$j 3이상 값부터 전부다 언셋하여 삭제함 *중요
        for ($j=3; $j < $count_a; $j++) {
            unset($companys[$i][$j]);
        }
    }
    //$companys의 값을 재정렬하여 $company에 대입한다
    //쉬운 방법이 더 있겠지만 도저히 생각이 안나서 나누어서 만듦
    $columns = array();
    $company = array();
    //$companys의 값을 $company로 대입한다
    //더 좋은 방법이 있을 것 같으나 내 머리로는 이게 한계점인듯함
    //내가 이해 할 수 있는 수준까지 나누어서 쿼리문 실행
    for ($i=0; $i < $columns_num; $i++) {
            $columns[$i] = array('type'    => 'buttons',
                                 'text'    => $select_text, //각 선택지별로 회사,부서,고용형태가 다르기에 변수로서 대입한다
                                 'actions' => $company[$i] = $companys[$i]
                                );
    }
    //복수 탬플릿용 타입 'type'    => 'carousel'
    $template = array('type'    => 'carousel',
                      'columns' => $columns
                     );

    //오직 모바일에서 보이기에 pc버젼일 경우 안내멘트 'altText'  => '代替テキスト'
    $message = array('type'     => 'template',
                     'altText'  => '代替テキスト',
                     'template' => $template
                    );
    return $message;
}
// 선택한게 맞는지 아닌지 확인한다
//yes&no함수 선택한 답이 맞으면 다음 단계, 아닐경우 되돌아간다
function line_bot_yes_no($select_data,$user_id,$yes,$no,$select_id){

    //선택한 데이터값이 맞는지 묻는다
    $template = array('type'    => 'confirm',
                      'text'    => '「'.$select_data.'」でよろしいでしょうか？',
                      'actions' => array(
                                        array(
                                              //data값으로 보낼시에 postback을 사용한다(보이지 않는 형태값)<=>text 보이는값
                                              'type' => 'postback',
                                              'label' => 'はい',
                                              //데이터 값에 yes&no/스탭/유저id/컬럼이름/컬럼id(아이디값으로 DB확인해야함)
                                              'data' => $yes.$user_id."/".$select_data."/".$select_id
                                             ),
                                        array(
                                              'type' => 'postback',
                                              'label' => 'いいえ',
                                              'data' => $no.$user_id."/".$select_data."/".$select_id
                                             )
                                       )
                    );
    //만약 pc로 볼경우 모바일로 보기위한 안내를 한다
    //탬플릿 선언
    $message = array('type'    => 'template',
                     'altText' => 'モバイルデバイスで確認できます',
                     'template' => $template
                    );
    //쎄팅한 값을 리턴 값으로 돌려준다
    return $message;
}
//현재 유저의 스탭이 몇 단계인지 확인한다.
//추후에 도중에 빠진 유저라도 도중부터 하게 할지
//처음부터 하게 할지 생각중*
$sql = "SELECT `user_id`,`step`
        FROM   `line_profile_step`";
if($res = $_link->query($sql)){
    $list = array();
    while($row = $res->fetch_array(MYSQLI_ASSOC)){
        $list[] = $row;
    }
    $count = count($list);
}

// var_dump($list);

//DB에서 현재 진행중인지 확인한다
//여러명 동시라도 처리 가능하게 만드는중
for ($i=0; $i < $count; $i++) {
    //만약 아이디가 있다면 스탭별로 넘어간다
    if($list[$i]['user_id'] == $event['source']['userId']){
        $user_id = $list[$i]['user_id'];
        $step = $list[$i]['step'];
        break;
    }else{
        $step = 0;
    }
}
unset($list);
$u_id = "'".$_link->real_escape_string($user_id)."'";

//현재는 숫자1이긴 하나 추후에 팔오우 상태일때로 바꿀 예정
//시작할때 팔로우시 시작하는 코드문
if($message_text == 1 || $step == 0){
        $message = array('type' => 'text',
                         'text' => 'こんにちは登録のため、名前だけ入力してください。例：山田 太郎'
                        );
        line_bot_reply($access_token,$url,$reply_token,$message);

    //추후에 이 조건을 위에 임시 조건과 변경함
    // + 팔로우를 걸었을 시
    if($step == 0){
        $user_id = "'".$_link->real_escape_string($event['source']['userId'])."'";
        $sql = "INSERT INTO `line_profile_step` (`user_id`,`step`)VALUES ({$user_id},1)";
        $res = $_link->query($sql);
    }
}

// 값을 추출하여 나눈다 0 => yes&no, 1 => 단계, 2 => 유저ID, 3 => 전달 이름 4 => 전달 값
if(isset($event['postback']['data'])){
    $postback = explode("/",$event['postback']['data']);
}

if ($step == 1 && isset($user_id) && !isset($postback[1])) {
    //「」안에 들어갈 선택한 값
    $select_data = $message_text.$event['postback']['data'];
    // 스탭별로 yes no + 단계 설정
    $yes = "YES/2/";
    $no = "NO/1/";
    //yes_no함수로 값을 만들어낸다
    $message = line_bot_yes_no($select_data,$user_id,$yes,$no);
    //최종값 출력
    line_bot_reply($access_token,$url,$reply_token,$message);
}else if($postback[0] == 'NO' && $postback[1] == '1'){
    $message = array('type' => 'text',
                     'text' => "お名前をもう一度入力お願いします。");
    line_bot_reply($access_token,$url,$reply_token,$message);
}

if (isset($user_id)) {
    switch ($postback[1]) {
        case '2':
            if ($postback[0] == "NO") {
                $message = array('type' => 'text',
                                 'text' => 'もう一度選択してください。');
                line_bot_reply($access_token,$url,$reply_token,$message);
            }else{
                $sql = "SELECT `user_id`
                        FROM `employee`
                        WHERE `user_id` = '{$user_id}'";
                if($res = $_link->query($sql)){
                    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                        $list[] = $row;
                    }
                    $count = count($list);
                }

                for ($i=0; $i < $count; $i++) {
                    // 값이 있다면 수정한다
                    if ($list[$i]['user_id'] == $user_id) {
                        $sql = "UPDATE `employee`
                                SET    `name` = '{$postback[3]}'
                                WHERE  `user_id` = {$u_id}";
                        if($res = $_link->query($sql)){
                            $sql_flg = '修正';
                        }
                    // 값이 없다면 신규입력한다
                    // }else{
                        // 신규입력
                        $sql = "INSERT INTO `employee`(`name`,`user_id`)
                                VALUES('{$postback[3]}','{$u_id}')";
                        if($res = $_link->query($sql)){
                            $sql_flg = '登録';
                        }
                    }
                }
                unset($list);
                // $list = json_encode($list);
                // DB에 등록완료 선언
                if($sql_flg == '登録'){
                    $sql_message = '登録しました。';
                }else if($sql_flg == '修正'){
                    $sql_message = '修正しました。';
                }else{
                    $sql_message = '登録に失敗しました。';
                }
                $message = array('type' => 'text',
                                 'text' => $sql_message);
                line_bot_reply($access_token,$url,$reply_token,$message);
            }

            $sql = "SELECT `id`,`company_name`
                    FROM   `company`";
            if($res = $_link->query($sql)){
                $list = array();
                while($row = $res->fetch_array(MYSQLI_ASSOC)){
                    $list[] = $row;
                }
                    $count = count($list);
            }
            $select_text = '会社名をお選びください。';
            $select_column = 'company_name';
            $select_id = 'id';
            $yes = 'YES/3/';
            $no = 'NO/2/';
            $message = line_bot_tamplate($user_id,$count,$list,$select_text,$select_column,$select_id,$yes,$no);
            unset($list,$count,$yes,$no);
            //탬플릿 값을 넣는다
            line_bot_push($access_token,$message,$user_id);
            break;
        case '3':
            //会社名Yes&No
            //「」안에 들어갈 선택한 값
            $select_data = $postback[3];
            $select_id = $postback[4];
            // 스탭별로 yes no + 단계 설정
            $yes = "YES/4/";
            $no = "NO/2/";
            //yes_no함수로 값을 만들어낸다
            $message = line_bot_yes_no($select_data,$user_id,$yes,$no,$select_id);
            //최종값 출력
            line_bot_reply($access_token,$url,$reply_token,$message);

            // $message = array('type' => 'text',
            //                  'text' => "3番".$select_data);
            // line_bot_reply($url,$reply_token,$message);

            break;
        case '4':
            // DB에 등록하기 아직안함
            if ($postback[0] == "NO") {
                $sql = "SELECT `step_text` FROM `line_profile_step` WHERE `user_id` = {$u_id}";
                if($res = $_link->query($sql)){
                    $list = array();
                    while($row = $res->fetch_array(MYSQLI_ASSOC)){
                        $list[] = $row;
                    }
                    $count = count($list);
                }
                $postback[4] = $list[0]['step_text'];
                unset($lsit,$sql,$count);

                $message = array('type' => 'text',
                                 'text' => $test0.'もう一度選択してください。');
                line_bot_reply($access_token,$url,$reply_token,$message);
            }else{
                //회사 정보를 기입한다
                $sql = "UPDATE `line_profile_step` SET `step_text` = '{$postback[4]}' WHERE `user_id` = {$u_id}";
                $res = $_link->query($sql);
                // step DB에  등록하기

                $message = array('type' => 'text',
                                 'text' => "登録しました。");
                line_bot_reply($access_token,$url,$reply_token,$message);
            }

            //사명을 고르면 그 회사 기준으로 각 부서별 목록이 나옴
            //부서까지 골라야지 유저 데이터에 입력이 가능함
            $sql = "SELECT `id`,`company_id`,`department_name`
                    FROM `department`";
            if($res = $_link->query($sql)){
                $department_list = array();
                while($row = $res->fetch_array(MYSQLI_ASSOC)){
                    $department_list[] = $row;
                }
                $department_count = count($department_list);
            }
            $list = array();
            for ($i=0; $i < $department_count; $i++) {
                if($department_list[$i]['company_id'] == $postback[4]){
                    $list[] = $department_list[$i];
                }
            }
            $count = count($list);

            $select_text = '部署名をお選びください。';
            $select_column = 'department_name';
            $select_id = 'id';
            $yes = 'YES/5/';
            $no = 'NO/4/';
            //空きテキスト 클릭시 회사 정보를 가지고 되돌아 와야함
            $null_text = './'.$postback[4];
            $message = line_bot_tamplate($user_id,$count,$list,$select_text,$select_column,$select_id,$yes,$no,$null_text);
            unset($list,$count);
            // 탬플릿 값을 넣는다
            line_bot_push($access_token,$message,$user_id);
            break;
        case '5':
                // 部署Yes&No
                //「」안에 들어갈 선택한 값
                $select_data = $postback[3];
                $select_id = $postback[4];
                // 스탭별로 yes no + 단계 설정
                $yes = "YES/6/";
                $no = "NO/4/";
                //yes_no함수로 값을 만들어낸다
                $message = line_bot_yes_no($select_data,$user_id,$yes,$no,$select_id);
                //최종값 출력
                line_bot_reply($access_token,$url,$reply_token,$message);
                break;
        case '6':
            // 선택한 부서가 음식업인지 확인한다
            // 음식업일 경우 점포위치도 설정
            if ($postback[0] == "NO") {
                $sql = "SELECT `step_text` FROM `line_profile_step` WHERE `user_id` = {$u_id}";
                if($res = $_link->query($sql)){
                    $list = array();
                    while($row = $res->fetch_array(MYSQLI_ASSOC)){
                        $list[] = $row;
                    }
                    $count = count($list);
                }
                $postback[4] = $list[0]['step_text'];
                unset($lsit,$sql,$count);

                $message = array('type' => 'text',
                                 'text' => "もう一度選択してください。".$postback[4]);
                line_bot_reply($access_token,$url,$reply_token,$message);
            }else{
                // DB에 등록하기
                // //먼저 정보를 찾아서 수정/신규 구분한다
                // $sql = "SELECT `user_id`
                //         FROM   `employee`
                //         WHERE  `user_id` = {$u_id}";
                // if($res = $_link->query($sql)){
                //     $list = array();
                //     while($row = $res->fetch_array(MYSQLI_ASSOC)){
                //         $list[] = $row;
                //     }
                //     $count = count($list);
                // }
                // for ($i=0; $i < $count; $i++) {
                //     // 정보 수정
                //     if ($list[$i]['user_id'] == $user_id) {
                        // code...
                        $sql = "UPDATE `employee`
                                SET    `department_id` = '{$postback[4]}'
                                WHERE  `user_id` = {$u_id}";
                        if($res = $_link->query($sql)){
                            $sql_message = '登録しました。';
                        }else{
                            $sql_message = '登録に失敗しました。';
                        }
                // 신규 입력
                //     }else{
                //         $sql = "INSERT INTO `employee`(`user_id`,`department_id`)
                //                 VALUES      ({$u_id},'{$postback[4]}')";
                //         if ($res = $_link->query($sql)) {
                //             // code...
                //             $sql_flg = '登録';
                //         }
                //     }
                // }
                unset($list,$count);

                $message = array('type' => 'text',
                                 'text' => $count.$sql_message.$list);
                line_bot_reply($access_token,$url,$reply_token,$message);

            //중요 DB에 들어가는 구문
            }

            // {$postback[4]} //부서 아이디 값
            //부서 아이디 값으로 점포수를 구한다(서브쿼리-이거 찾느라 inner조인등 찾느라 시간 걸림)
            $sql = "SELECT `id`,`store_name`
                    FROM `store`
                    WHERE `company_id` = (SELECT `company_id`
                					      FROM `department`
                					      WHERE `id` = '{$postback[4]}')";
            if($res = $_link->query($sql)){
                $list = array();
                while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                    $list[] = $row;
                }
                $count = count($list);
            }
            // $list = json_encode($list);

            //여기서 값이 있을경우 점포가 있는거니 그대로 진행함
            //값이 없을 경우 바로 다음 고용형태로 넘어감
            if (!empty($count)) {
                $select_text = '店舗をお選びください。';
                $select_column = 'store_name';
                $select_id = 'id';
                $yes = 'YES/8/';
                $no = 'NO/6/';
                //空きテキスト 클릭시 회사 정보를 가지고 되돌아 와야함
                $null_text = './'.$postback[4];
                $message = line_bot_tamplate($user_id,$count,$list,$select_text,$select_column,$select_id,$yes,$no,$null_text);
                unset($list,$count);
                // 탬플릿 값을 넣는다
                line_bot_push($access_token,$message,$user_id);
                break;
            }
            // $postback[1] = 7;
            // continue;
            //점포가 없는경우 브레이크 받지 않고 바로 고용형태로 넘어간다.
        case '7':
            // 만약 점포를 골랐다면 여기서 DB에 넣어줘야 한다.
            if ($postback[0] == "NO") {
                $message = array('type' => 'text',
                                 'text' => "もう一度選択してください。");
                line_bot_reply($access_token,$url,$reply_token,$message);
            }else if($postback[1] == 7){

                //먼저 이 값이 맞는지 틀린지 확인할 것!

                // code...
                //점포명 DB 저장
                //먼저 정보가 있는지 없는지 검사부터 한다 있으면 수정 아님 신규 입력
                //수정
                $sql = "UPDATE `employee` SET `store_id` = '{$postback[4]}' WHERE `user_id` = {$u_id}";
                if($res = $_link->query($sql)){
                    $sql_message = "登録しました。";
                }else{
                    $sql_message = "登録に失敗しました。";
                }
                $message = array('type' => 'text',
                                 'text' => $sql_message);
                line_bot_reply($access_token,$url,$reply_token,$message);
            }


            // code...고용형태
            $sql = "SELECT `id`,`employment_type`
                    FROM `employment`";
            if($res = $_link->query($sql)){
                $list = array();
                while($row = $res->fetch_array(MYSQLI_ASSOC)){
                    $list[] = $row;
                }
            }
            $count = count($list);
            $select_text = '雇用形態をお選びください。';
            $select_column = 'employment_type';
            $select_id = 'id';
            $yes = 'YES/8/';
            $no = 'NO/6/';
            $message = line_bot_tamplate($user_id,$count,$list,$select_text,$select_column,$select_id,$yes,$no);
            unset($list,$count);
            // 탬플릿 값을 넣는다
            line_bot_push($access_token,$message,$user_id);

            break;
        case '8':
            // 점포 Yes&No
            //「」안에 들어갈 선택한 값
            $select_data = $postback[3];
            $select_id = $postback[4];
            // 스탭별로 yes no + 단계 설정
            $yes = "YES/7/";
            $no = "NO/6/";
            //yes_no함수로 값을 만들어낸다
            $message = line_bot_yes_no($select_data,$user_id,$yes,$no,$select_id);
            //최종값 출력
            line_bot_reply($access_token,$url,$reply_token,$message);

            break;
        case '9':
            // 雇用形態Yes&No
            //「」안에 들어갈 선택한 값
            $select_data = $postback[3];
            $select_id = $postback[4];
            // 스탭별로 yes no + 단계 설정
            $yes = "YES/10/";
            $no = "NO/7/";
            //yes_no함수로 값을 만들어낸다
            $message = line_bot_yes_no($select_data,$user_id,$yes,$no,$select_id);
            //최종값 출력
            line_bot_reply($access_token,$url,$reply_token,$message);
            break;
        case '10':
            if ($postback[0] == "NO") {
                $message = array('type' => 'text',
                'text' => "もう一度選択してください。");
                line_bot_reply($access_token,$url,$reply_token,$message);
            }else{
                //먼저 정보가 있는지 없는지 검사부터 한다 있으면 수정 아님 신규 입력
                //수정
                $sql = "UPDATE `employee` SET `employment_id` = '{$postback[4]}' WHERE `user_id` = {$u_id}";
                if($res = $_link->query($sql)){
                    $sql_message = "登録しました。";
                }

                //신규.. 아니 잠깐..? 이미 후반인데 정보가 신규 일 수 있나?
                // $sql = "INSERT INTO `employee` (`store_id`) VALUES ('{$postback[4]}')";
                //점포명 DB 저장
                // 지금까지 선택한 정보를 모두 표시하고 이게 맞는지 확인한다.
                // 아닐경우 처음부터 다시 시작한다
                // 여기서 고용형태 저장한다
                $message = array('type' => 'text',
                'text' => $sql_message);
                line_bot_reply($access_token,$url,$reply_token,$message);
            }
            // code...

            break;
        default:
            $message = array('type' => 'text',
                             'text' => "失敗しました".$postback[1]);
            line_bot_reply($access_token,$url,$reply_token,$message);
            break;
    }
}
