<?php
//세션 사용
session_start();
// 은혜 DB연결
require_once('./require2/mysql.php');
$access_token = 'Jc56AtUGFZP3rgIY0bMRCl7FQ7nMn3WlAbTD/P2CACKI/cJ0AhkIIeE0StnEP4kfd/mEp3mgPtT6v1owYiCxG3MrgpZjQ32LDxW3vWPjNr/LOVnJmbLzTYD4801aF8ipZhTGBhnPxAn2s+C7k23ulwdB04t89/1O/w1cDnyilFU=';
//라인에서 정보를 가져온다
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);
$event = $receive['events'][0];
$event_type = $event['type'];
//유저 아이디 찾기
function user_select($user_id,$_link){
    $db_user_id = "'".$_link->real_escape_string($user_id)."'";
    $sql = "SELECT `user_id`,`status` FROM `line_info` WHERE `user_id` = {$db_user_id}";
    if($res = $_link->query($sql)){
        $list = array();
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $list[] = $row;
        }
    }
    return $list;
}
//유저 display_name찾기
function display_name($user_id,$access_token){
    $url = "https://api.line.me/v2/bot/profile/{$user_id}";
    $headers = array('Authorization: Bearer ' . $access_token);
    $options = array(
        CURLOPT_URL            => $url,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers
    );
    $curl = curl_init();
    curl_setopt_array($curl,$options);
    $res = curl_exec($curl);
    $res = json_decode($res,true);
    curl_close($curl);
    return $res;
}
//출퇴근 결과 리플레이
function line_reply($messages,$reply_token,$access_token){
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    );
    $message = array(
        'type' => 'text',
        'text' => $messages
    );
    $body = json_encode(
        array(
            'replyToken'=>$reply_token,
            'messages'=>array($message)
        )
    );
    $options = array(
        CURLOPT_URL=>'https://api.line.me/v2/bot/message/reply',
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $body);
    $curl = curl_init();
    curl_setopt_array($curl, $options);
    curl_exec($curl);
    curl_close($curl);
}
//출퇴근 결과 리플레이 템플릿
function line_reply_template($message,$reply_token,$access_token){
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    );
    $body = json_encode(
        array(
            'replyToken'=>$reply_token,
            'messages'=>array($message)
        )
    );
    $options = array(
        CURLOPT_URL=>'https://api.line.me/v2/bot/message/reply',
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $body);
    $curl = curl_init();
    curl_setopt_array($curl, $options);
    curl_exec($curl);
    curl_close($curl);
}
//메세지 송신 푸쉬
function line_push($to,$messages,$access_token){
    $headers = array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Bearer ' . $access_token
    );
    $message = array(
        'type' => 'text',
        'text' => $messages
    );
    // build request body
    $body = json_encode(
        array(
            'to' => $to,
            'messages'   => array($message)
        )
    );
    $options = array(
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $body
    );
    $curl = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt_array($curl, $options);
    $result = curl_exec($curl);
    $error = curl_errno($curl);
    if($error){
        return;
    }
}
//메세지 송신 푸쉬 템플릿
function line_pish_template($to,$message,$access_token){
    $headers = array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Bearer ' . $access_token
    );
    // build request body
    $body = json_encode(
        array(
            'to' => $to,
            'messages'   => array($message)
        )
    );
    $options = array(
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $body
    );
    $curl = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt_array($curl, $options);
    $result = curl_exec($curl);
    $error = curl_errno($curl);
    if($error){
        return;
    }
}
//근무자 상태 구하기
function employee_status($user_status,$_link){
    $sql ="SELECT id,
    case when punch_out is null then punch_in
     else punch_out END as status,
     case when punch_out is null then '出勤'
     else '退勤' END as user_status
    FROM attendance
    WHERE `employee_id`='{$user_status}'
    order by status desc
    LIMIT 0,1";
    if($res=$_link->query($sql)){
        $attendance_list = array();
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $attendance_list[] = $row;
        }
    }
    $sql ="SELECT `id`,`employee_id`,
     case
      when end_time is null then start_time
      else end_time end as status,
     case
      when end_time is null then '休始'
      else '休終' end as user_status
     FROM rest_time
     WHERE `employee_id`='{$user_status}'
      order by status desc
     LIMIT 0,1";
     if($res=$_link->query($sql)){
         $rest_time_list = array();
         while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
             $rest_time_list[] = $row;
         }
     }
     if(!empty($attendance_list)){
        if(!empty($rest_time_list)){
            if($attendance_list[0]['user_status'] == '出勤'){
                if($rest_time_list[0]['user_status'] == '休始'){
                    if($rest_time_list[0]['status'] > $attendance_list[0]['status']){
                        $employee_status = 2;//休憩
                    }else{
                        $employee_status = 1;//勤務
                    }
                }else if($rest_time_list[0]['user_status'] == '休終'){
                    if($rest_time_list[0]['status'] > $attendance_list[0]['status']){
                        $employee_status = 3;//勤務（休憩済み）
                    }else{
                        $employee_status = 1;//勤務
                    }
                }
            }else if($attendance_list[0]['user_status'] == '退勤'){
                if($rest_time_list[0]['user_status'] == '休始'){
                    if($attendance_list[0]['status'] > $rest_time_list[0]['status']){
                        $employee_status = 4;//退勤
                    }else{
                        $employee_status = 2;//休憩（出勤無し）
                    }
                }else if($rest_time_list[0]['user_status'] == '休終'){
                    if($attendance_list[0]['status'] > $rest_time_list[0]['status']){
                        $employee_status = 4;//退勤
                    }else{
                        $employee_status = 1;//勤務（勤務・休始無し）
                    }
                }
            }
        }else if($attendance_list[0]['user_status'] == '出勤'){
            $employee_status = 1;//勤務
        }else if($attendance_list[0]['user_status'] == '退勤'){
            $employee_status = 4;//退勤
        }
    }else if(!empty($rest_time_list)){
        if($rest_time_list[0]['user_status'] == '休始'){
            $employee_status = 2;//休憩
        }else if($rest_time_list[0]['user_status'] == '休終'){
            $employee_status = 3;//勤務（休憩済み）
        }
    }else{
         $employee_status = 0;
    }
    return $employee_status;
}
//근무자 table정보 구하기
function employee_info($user_id,$_link){
    $sql ="SELECT `id`,`name`,`department_id` FROM `employee` WHERE `id` = (
        SELECT `employee_id` FROM `line_relation` WHERE `line_id` = (
            SELECT `id` FROM `line_info` WHERE  `user_id` = '{$user_id}'
        )
    )";
    if($res = $_link->query($sql)){
        $employee_list = array();
        while ($row = $res->fetch_array(MYSQLI_ASSOC)){
            $employee_list[] = $row;
        }
    }
    return $employee_list;
}
//출근 아이디 구하기 퇴근용
function attendance_id($employee_list_id,$_link){
    $sql = "SELECT `id` FROM `attendance` WHERE `employee_id` = '{$employee_list_id}' ORDER BY `punch_in` DESC LIMIT 0,1";
    if($res = $_link->query($sql)){
        $attendance_id = array();
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $attendance_id[] = $row;
        }
    }
    return $attendance_id;
}
//휴식 아이디 구하기 휴식끝용
function rest_time_id($employee_list_id,$_link){
    $sql = "SELECT `id` FROM `rest_time` WHERE `employee_id` = '{$employee_list_id}' ORDER BY `start_time` DESC LIMIT 0,1";
    if($res = $_link->query($sql)){
        $rest_id = array();
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $rest_id[] = $row;
        }
    }
    return $rest_id;
}
//친구추가
if($event_type == 'follow'){
    //팔로우 상태는 반드시 ID가 존재함
    $user_id = $event['source']['userId'];
    $list = user_select($user_id,$_link);
}else if($event_type == 'message'){
    if($event['source']['type'] == 'user'){
        //1:1대화는 반드시 ID가 존재함
        $user_id = $event['source']['userId'];
        $list = user_select($user_id,$_link);
    }
}
//유저 정보 추가
if(!empty($list) && $event_type == 'follow'){
    $to = $user_id;
    //이미 등록된 유저인지 확인한다.
    if($list[0]['user_id'] == $user_id){
        $messages = '登録済みのユーザーです。';
    }
}else if(empty($list)){
    $to = $user_id;
    $res = display_name($user_id,$access_token);
    $db_user_id = "'".$_link->real_escape_string($user_id)."'";
    $display_name = $res['displayName'];
    unset($res);
    $sql = "INSERT INTO `line_info` (`user_id`,`display_name`,`status`) VALUES ({$db_user_id},'{$display_name}',0)";
    if(!empty($db_user_id) && !empty($display_name)){
        if($res = $_link->query($sql)){
            $messages = '登録ありがとうございます。';
        }else{
            $messages = '登録に失敗しました。';
        }
    }
}
//登録挨拶
if(isset($to)){
    line_push($to,$messages,$access_token);
}
//1차 에러처리 エラー処理
if($event_type == 'message'){
    //리플라이토큰 작성
    $reply_token = $event['replyToken'];
    //그룹이라면 지정그룹인지 아닌지 판별한다.
    if(isset($event['source']['groupId'])){
        //지정 그룹을 안내한다.
        $messages = '申し訳ございません、1：1のチャットのみ対応します。';
        line_reply($messages,$reply_token,$access_token);
    //룸이라면
    }else if(isset($event['source']['roomId'])){
        //지정 그룹을 안내한다.
        $messages = '申し訳ございません、1：1のチャットのみ対応します。';
        line_reply($messages,$reply_token,$access_token);
    //개인으로 활성화 된 사람이라면 현재 상태를 물어 볼 수 있다.
    }else if(isset($event['source']['userId'])){
        $user_id = $event['source']['userId'];
        $user_list = user_select($user_id,$_link);
        if($user_list[0]['status'] == 1){
            $attendance_start = true;
            if(isset($event['message']['packageId'])){
                $package_id = $event['message']['packageId'];
                $sticker_id = $event['message']['stickerId'];
                //라인 곰돌이가 ?? 하는 스티커
                if($package_id == 11537 && $sticker_id == 52002744){
                    $employee_list = employee_info($user_id,$_link);
                    $user_status = $employee_list[0]['id'];
                    $employee_status = employee_status($user_status,$_link);
                    switch ($employee_status){
                        case '0':
                            $status_message = 'まだ勤務したことがありません、これから頑張りましょう！！';
                            break;
                        case '1':
                            $status_message = '現在は勤務中です、出勤した時間は「';
                            $sql = "SELECT `punch_in` FROM `attendance` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `punch_in` DESC LIMIT 0,1";
                            $column = 'punch_in';
                            break;
                        case '2':
                            $status_message = '現在は休憩中です、休憩を始めた時間は「';
                            $sql = "SELECT `start_time` FROM `rest_time` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `start_time` DESC LIMIT 0,1";
                            $column = 'start_time';
                            break;
                        case '3':
                            $status_message = '現在は勤務中です、休憩が終わった時間は「';
                            $sql = "SELECT `end_time` FROM `rest_time` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `end_time` DESC LIMIT 0,1";
                            $column = 'end_time';
                            break;
                        case '4':
                            $status_message = '勤務外です、最後に退勤した時間は「';
                            $sql = "SELECT `punch_out` FROM `attendance` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `punch_out` DESC LIMIT 0,1";
                            $column = 'punch_out';
                            break;
                    }
                    if($res = $_link->query($sql)){
                        $start_message_list = array();
                        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                            $start_message_list[] = $row;
                        }
                    }
                    $messages = $status_message.$start_message_list[0][$column].'」です。'.$test000.$test001.$attendance_list.$rest_time_list;
                    //지정 그룹을 안내한다.
                    line_reply($messages,$reply_token,$access_token);
                }
            }else{
                $messages = '自分の勤怠状況をわかるためには以下のスタンプを押してください。';
                line_reply($messages,$reply_token,$access_token);
                $to = $user_id;
                $message = array(
                    'type' => 'sticker',
                    'packageId' => 11537,
                    'stickerId' => 52002744
                );
                line_pish_template($to,$message,$access_token);
            }
        }else{
            $messages = 'IDは存在しますがサーバーに登録手続きが終わっていません。';
            line_reply($messages,$reply_token,$access_token);
        }
    }
}
//2차 출퇴근현황 및 에러검사
if($attendance_start && isset($event['message']['packageId'])){
    $user_id = $event['source']['userId'];
    $employee_list = employee_info($user_id,$_link);
    $user_status = $employee_list[0]['id'];
    $employee_status = employee_status($user_status,$_link);
    if(isset($employee_list[0]['id'])){
        $employee_list_id = $employee_list[0]['id'];
        //명령이 들어온 순간 가장 마지막 명령시간을 찾아본다
        //1분이내에 같은 명령이 있는지ｘｘｘ 명령이 연속 두 번 오는지 확인한다
        switch ($employee_status){
            case '1':
                $sql = "SELECT `punch_in` FROM `attendance` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `punch_in` DESC LIMIT 0,1";
                $column = 'punch_in';
            break;
            case '2':
                $sql = "SELECT `start_time` FROM `rest_time` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `rest_time` DESC LIMIT 0,1";
                $column = 'start_time';
            break;
            case '3':
                $sql = "SELECT `end_time` FROM `rest_time` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `end_time` DESC LIMIT 0,1";
                $column = 'end_time';
            break;
            case '4':
                $sql = "SELECT `punch_out` FROM `attendance` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `punch_out` DESC LIMIT 0,1";
                $column = 'punch_out';
            break;
        }
        if($res = $_link->query($sql)){
            $start_message_list = array();
            while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $start_message_list[] = $row;
            }
        }
        $date = strtotime($start_message_list[0][$column].'+1 minutes');
        $date = date('Y-m-d H:i:s', $date);
        $now_date = date('Y-m-d H:i:s');
        $falst = false;
        if($falst){
            $messages = '1分後にまたお願いします';
            line_reply($messages,$reply_token,$access_token);
        }else{
            $package_id = $event['message']['packageId'];
            $sticker_id = $event['message']['stickerId'];
            $date = date('Y-m-d H:i:s');
            if($package_id == '11537'){
                switch ($sticker_id) {
                    case '52002738':
                        if($employee_status == 1 || $employee_status == 3){
                            //출근중에 출근 yes&no
                            $template_message = '現在、勤務中です、もう一度出勤しますか？';
                            $template_data = '/1/1';
                        }else if($employee_status == 2){
                            //휴식중에 출근 yes&no
                            $template_message = '現在、休憩中です、出勤しますか？';
                            $template_data = '/2/1';
                        }else if($employee_status == 0 || $employee_status == 4){
                            //정상적인 출근
                            $output_message = '出勤しました。';
                            $sql = "INSERT INTO `attendance` (`employee_id`,`punch_in`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                        }
                    break;
                    case '52002745':
                        if($employee_status == 2){
                            //휴식중에 휴식
                            $template_message = '現在、休憩中です、もう一度休憩しますか？';
                            $template_data = '/2/2';
                            // $group_message = '現在は休憩中です、もう一度休憩しますか？';
                        }else if($employee_status == 4){
                            //퇴근중에 휴식
                            $template_message = '現在、退勤状態です、休憩しますか？';
                            $template_data = '/4/2';
                            // $group_message = '現在は退勤状態です、休憩しますか？';
                        }else if($employee_status == 1 || $employee_status == 3){
                            //정상적인 휴식
                            $output_message = '休憩入りました。';
                            $sql = "INSERT INTO `rest_time` (`employee_id`,`start_time`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                        }else if($employee_status == 0){
                            $output_message = '最初は出勤からお願いします。';
                        }
                    break;
                    case '52002751':
                        if($employee_status == 1 || $employee_status == 3){
                            //출근중에 휴식끝
                            $template_message = '現在、勤務中です、休憩終了しますか？';
                            $template_data = '/1/3';
                            // $group_message = '現在は勤務中です、休憩終了しますか？';
                        }else if($employee_status == 4){
                            //퇴근중에 휴식끝
                            $template_message = '現在、退勤状態です、休憩終了しますか？';
                            $template_data = '/4/3';
                            // $group_message = '現在は退勤状態です、休憩終了しますか？';
                        }else if($employee_status == 2){
                            //정상적인 휴식끝
                            $output_message = '休憩終わりました。';
                            //가장 최신인 휴식을 찾아서 변경한다.
                            $rest_id = rest_time_id($employee_list_id,$_link);
                            $sql = "UPDATE `rest_time` SET `end_time` = '{$date}' WHERE `id` = '{$rest_id[0]['id']}'";
                        }else if($employee_status == 0){
                            $output_message = '最初は出勤からお願いします。';
                        }
                    break;
                    case '52002739':
                        if($employee_status == 2){
                            //휴식중에 퇴근
                            $template_message = '現在、休憩中です、退勤しますか？';
                            $template_data = '/2/4';
                        }else if($employee_status == 4){
                            //퇴근중에 퇴근
                            $template_message = '現在、退勤状態です、退勤しますか？';
                            $template_data = '/4/4';
                        }else if($employee_status == 1 || $employee_status == 3){
                            $attendance_id = attendance_id($employee_list_id,$_link);
                            $output_message = '退勤しました。';
                            $sql = "UPDATE `attendance` SET `punch_out` = '{$date}' WHERE `id` = '{$attendance_id[0]['id']}'";
                        }else if($employee_status == 0){
                            $output_message = '最初は出勤からお願いします。';
                        }
                    break;
                }
            }
            //들어온 값이 출퇴근인지 휴식인지 분기한다.
            if(isset($output_message)){
                //sql을 실행한다
                $res = $_link->query($sql);
                $messages = $employee_list[0]['name'].'さん'.$output_message;
                line_reply($messages,$reply_token,$access_token);
            }else if(isset($template_message)){
                $_SESSION['template_status'] = 1;
                $template = array(
                    'type' => 'confirm',
                    'text' => $employee_list[0]['name'].'さんは'.$template_message,
                    'actions' => array(
                        //여기에 메세지 보낼 1주소 (리플레이,푸쉬) 보낼 정보 2예스&노 3어떤정보인지
                        array('type' => 'postback', 'label' => 'はい', 'data' => 'yes/'.$user_id.$template_data),
                        array('type' => 'postback', 'label' => 'いいえ', 'data' => 'no/'.$user_id)
                    )
                );
                $message = array(
                    'type'    => 'template',
                    'altText' => 'モバイルデバイスで確認できます',
                    'template' => $template
                );
                line_reply_template($message,$reply_token,$access_token);
            }
        }
    }
}
//3차 에러상황 Yes&No 선택
if(isset($event['postback']) && isset($event['source']['userId'])){
    $user_id = $event['source']['userId'];
    $employee_list = employee_info($user_id,$_link);
    $employee_list_id = $employee_list[0]['id'];
    // if($_SESSION['template_status'] == 1){
    //     $reply_token = $event['replyToken'];
    //     $messages = "入りました。".$event['postback']['data'];
    //     line_reply($messages,$reply_token,$access_token);
    // }
    if($_SESSION['template_status'] == 1){
        unset($_SESSION['template_status']);
        $reply_token = $event['replyToken'];
        //제일처음 에러를 부른사람과 탬플렛 클릭하는 사람이 같아야함
        //0:yes,no 1:user_id 2:현재상태 3:시도항목
        $postback = explode('/',$event['postback']['data']);
        if($event['source']['userId'] == $postback[1]){
            $user_id = $postback[1];
            $date = date('Y-m-d H:i:s');
            if($postback[0] == 'yes'){
                switch ($postback[2]) {
                    case '1':
                        switch ($postback[3]) {
                            case '1':
                                //出勤中に出勤 출근새로 추가후 휴식&출근 정보 덮고  *누락정보 전날 퇴근
                                $sql = "INSERT INTO `attendance` (`employee_id`,`punch_in`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                                if($res = $_link->query($sql)){
                                    $reply_error_text = '出勤中に出勤しました。（前日の退勤情報なし）';
                                }
                                break;
                            case '3':
                                //出勤中に休憩終わり 휴식시작 정보가 없으므로 휴식끝으로 인설트 함 *누락정보 휴식시작
                                $sql = "INSERT INTO `rest_time` (`employee_id`, `end_time`) VALUES ('{$employee_list[0]['id']}', '{$date}')";
                                if($res = $_link->query($sql)){
                                    $reply_error_text = '出勤中に休憩終わりました。（休憩始め情報なし）';
                                }
                                break;
                        }
                        break;
                    case '2':
                        switch ($postback[3]) {
                            case '1':
                                //休憩中に出勤 출근정보추가 휴식중에 출근 이전 정보 덮고  *누락정보 휴식끝 퇴근
                                $sql = "INSERT INTO `attendance` (`employee_id`,`punch_in`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                                if($res = $_link->query($sql)){
                                    $reply_error_text = '休憩中に出勤しました。（休憩終わり・退勤情報なし）';
                                }
                                break;
                            case '4':
                                //休憩中に退勤 퇴근을한다 휴식중인 정보를 덮고  *누락정보 휴식끝
                                $attendance_id = attendance_id($employee_list_id,$_link);
                                $sql = "UPDATE `attendance` SET `punch_out` = '{$date}' WHERE `id` = '{$attendance_id[0]['id']}'";
                                if($res = $_link->query($sql)){
                                    $reply_error_text = '休憩中に退勤しました。（休憩終わり情報なし）';
                                }
                                break;
                            case '2':
                                //休憩中に休憩します。휴식중에 휴식을 합니다. *누락정보 휴식끝
                                $sql = "INSERT INTO `rest_time` (`employee_id`,`start_time`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                                if($res = $_link->query($sql)){
                                    $reply_error_text = '休憩中に休憩しました。（休憩終わり情報なし）';
                                }
                                break;
                        }
                        break;
                    case '4':
                        switch ($postback[3]) {
                            case '2':
                                //退勤中に休憩 그날 기준으로 더미 출근정보를 만들고 휴식을 시작한다 *누락정보 출근
                                //더미 출근 정보 연/월/일  시간정보 없음
                                //ver2)휴식정보 개선후 가짜 출근정보 없이 다이렉트로 휴식정보를 입력하게 바꿈
                                $sql = "INSERT INTO `rest_time` (`employee_id`,`start_time`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                                if($res = $_link->query($sql)){
                                    $reply_error_text = '退勤中に休憩しました。（出勤情報なし）';
                                }
                                break;
                            case '3':
                                //退勤中に休憩終わり 그날 기준으로 더미 출근정보 만들고 휴식을 끝을한다 *누락정보 출근, 휴식시작
                                $sql = "INSERT INTO `rest_time` (`employee_id`,`end_time`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                                if($res = $_link->query($sql)){
                                    $reply_error_text = '退勤中に休憩終わりました。（出勤・休憩始め情報なし）';
                                }
                                break;
                            case '4':
                                //退勤中に退勤しました
                                $sql = "INSERT INTO `attendance`(`employee_id`,`punch_out`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                                if($res = $_link->query($sql)){
                                    $reply_error_text = '退勤中に退勤しました。（出勤情報なし）';
                                }
                                break;
                        }
                        break;
                }
                $messages = $reply_error_text.$sql;
                line_reply($messages,$reply_token,$access_token);
            }else if($postback[0] == 'no'){
                $messages = 'いいえを選択しました。';
                line_reply($messages,$reply_token,$access_token);
            }
            if(isset($reply_error_text)){
                //관리자 유저Id를 구할 회사 아이디를 구하는 쿼리
                $sql = "SELECT `id` FROM `employee` WHERE `department_id` =
                (SELECT `company_id` FROM `department` WHERE `id` = '{$employee_list[0]['department_id']}')";
                if($res = $_link->query($sql)){
                    $employee_id_list = array();
                    while($row = $res->fetch_array(MYSQLI_ASSOC)){
                        $employee_id_list[] = $row;
                    }
                    $count = count($employee_id_list);
                }
                $values = array();
                for ($i=0; $i < $count; $i++) {
                    if($i == 0){
                        $values[] = "`employee_id` = '{$employee_id_list[$i]['id']}'";
                    }else{
                        $values[] = "OR `employee_id` = '{$employee_id_list[$i]['id']}'";
                    }
                }
                unset($employee_id_list,$count);
                $values = implode(' ',$values);
                $sql = "SELECT `line_id` FROM `line_relation` WHERE ".$values;
                if($res = $_link->query($sql)){
                    $employee_id_list = array();
                    while($row = $res->fetch_array(MYSQLI_ASSOC)){
                        $employee_id_list[] = $row;
                    }
                    $count = count($employee_id_list);
                }
                unset($values);
                $values = array();
                for ($i = 0; $i < $count; $i++) {
                    if ($i == 0) {
                        $values[] = "`id` = '{$employee_id_list[$i]['line_id']}'";
                    }else{
                        $values[] = "OR `id` = '{$employee_id_list[$i]['line_id']}'";
                    }
                }
                unset($employee_id_list,$count);
                $values = implode(' ',$values);
                $sql = "SELECT `user_id` FROM `line_info` WHERE".$values;
                if($res = $_link->query($sql)){
                    $employee_id_list = array();
                    while($row = $res->fetch_array(MYSQLI_ASSOC)){
                        $employee_id_list[] = $row;
                    }
                    $count = count($employee_id_list);
                }
                $test = json_encode($employee_list);
                $messages = $employee_list[0]['name'].'さんは'.$reply_error_text;
                line_reply($messages,$reply_token,$access_token);
                //에러시 나에게로 문자를 보냄(임시)
                // for ($i=0; $i < $count; $i++) {
                //     $to = $employee_id_list[$i]['user_id'];
                //     line_push($to,$messages,$access_token);
                // }
                $to = 'Ue6824041a4f68a7ef205494623659b08';
                line_push($to,$messages,$access_token);
            }
        }
    }
}
 ?>
