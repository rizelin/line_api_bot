<?php
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
//근무자 table정보 구하기
function employee_info($user_id,$_link){
    $sql ="SELECT `id`,`name`,`attendance_status`,`department_id`,`attendance_id`,`rest_time_id` FROM `employee` WHERE `id` = (
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
//그룹정보 구하기
function line_group_info($employee_list_id,$_link){
    $sql = "SELECT `id`,`line_group_id`,`template_status` FROM `line_info_group` WHERE `id` =
    (SELECT `line_group_id` FROM `company` WHERE `id` =
        (SELECT `company_id` FROM `department` WHERE `id`=
            (SELECT `department_id` FROM `employee` WHERE `id` = '{$employee_list_id}')))";
    if($res = $_link->query($sql)){
        $group_id = array();
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $group_id[] = $row;
        }
    }
    return $group_id;
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
        //그룹이라면 지정 그룹인지 확인한다
        $sql = "SELECT `line_group_id` FROM `line_info_group` WHERE `line_group_id` = '{$event['source']['groupId']}'";
        if($res = $_link->query($sql)){
            $group_list = array();
            while($row = $res->fetch_array(MYSQLI_ASSOC)){
                $group_id[] = $row;
            }
            $count = count($group_id);
        }
        //전체 지정 그룹안에 속하는지 확인한다 //정확한 지정그룹확인은 다음에
        //지정된 그룹이라면 //등록유저인지 미등록유저인지 판별한다
        if($event['source']['groupId'] == $group_id[0]['line_group_id']){
            //아이디는 있으나 미등록인 경우
            if(isset($event['source']['userId'])){
                $user_id = $event['source']['userId'];
                $list = user_select($user_id,$_link);
                if(isset($list[0]['user_id']) && $list[0]['status'] == 1){
                    // $group_message = 'ID登録ユーザーです';
                    //１次エラー検査終了 1차 에러검사 종료 출퇴근으로 넘어간다
                    // $attendance_start = true;
                    //아이디는 존재하나 서버 등록이 아직입니다.
                }else if(isset($list[0]['user_id']) && $list[0]['status'] == 0){
                    $group_message = 'IDは存在しますがサーバーに登録手続きが終わっていません。';
                }else{
                    $group_message = '登録されていません、友たち登録お願いします。';
                }
            //아이디가 없는상태 1:1대화나 친구추가 부탁드립니다.
            }else{
                $group_message = '登録されていません、友たち登録お願いします。';
            }
            //지정그룹이 아니라면
        }else{
            $group_message = '指定された、グループではありません。指定のグループでお願いいたします。';
        }

        if(isset($group_message)){
            $messages = $group_message;
            line_reply($messages,$reply_token,$access_token);
        }
    //룸이라면
    }else if(isset($event['source']['roomId'])){
        //지정 그룹을 안내한다.
        $messages = '指定された、グループではありません。指定のグループでお願いいたします。';
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
                    switch ($employee_list[0]['attendance_status']){
                        case '0':
                            $status_message = 'まだ勤務したことがありません、これから頑張りましょう！！';
                            break;
                        case '1':
                            $status_message = '現在は勤務中です、出勤した時間は「';
                            $sql = "SELECT `punch_in` FROM `attendance` WHERE `id` = '{$employee_list[0]['attendance_id']}'";
                            $column = 'punch_in';
                            break;
                        case '2':
                            $status_message = '現在は休憩中です、休憩を始めた時間は「';
                            $sql = "SELECT `start_time` FROM `rest_time` WHERE `id` = '{$employee_list[0]['rest_time_id']}'";
                            $column = 'start_time';
                            break;
                        case '3':
                            $status_message = '現在は勤務中です、休憩が終わった時間は「';
                            $sql = "SELECT `end_time` FROM `rest_time` WHERE `attendance_id` = '{$employee_list[0]['attendance_id']}' ORDER BY `end_time` DESC";
                            $column = 'end_time';
                            break;
                        case '4':
                            $status_message = '勤務中ではありません、最後に退勤した時間は「';
                            $sql = "SELECT `punch_out` FROM `attendance` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `punch_out` DESC";
                            $column = 'punch_out';
                            break;
                    }
                    if($res = $_link->query($sql)){
                        $start_message_list = array();
                        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                            $start_message_list[] = $row;
                        }
                    }
                    $messages = $status_message.$start_message_list[0][$column].'」です。';
                    //지정 그룹을 안내한다.
                    line_reply($messages,$reply_token,$access_token);
                }
            }else{
                $messages = '自分の勤怠状況をわかるためには以下のスタンプを押してください。';
                line_reply($messages,$reply_token,$access_token);
                $to = $user_id;
                $message = array('type' => 'sticker'
                                ,'packageId' => 11537
                                ,'stickerId' => 52002744);
                line_pish_template($to,$message,$access_token);
            }
        }else{
            $messages = 'IDは存在しますがサーバーに登録手続きが終わっていません。';
            line_reply($messages,$reply_token,$access_token);
        }
    }
}
//2차 출퇴근현황 및 에러검사
if($attendance_start){
    if(isset($event['message']['packageId'])){
        $user_id = $event['source']['userId'];
        $employee_list = employee_info($user_id,$_link);
        //지정그룹이 정확한지 확인한다
        if(isset($employee_list[0]['id'])){
            $employee_list_id = $employee_list[0]['id'];
            $group_id = line_group_info($employee_list_id,$_link);
            //정확하게 지정된 그룹인지 확인
            // if($group_id[0]['line_group_id'] == $event['source']['groupId']){
                //명령이 들어온 순간 가장 마지막 명령시간을 찾아본다
                //1분이내에 같은 명령이 있는지ｘｘｘ 명령이 연속 두 번 오는지 확인한다
                switch ($employee_list[0]['attendance_status']){
                    case '1':
                        $sql = "SELECT `punch_in` FROM `attendance` WHERE `id` = '{$employee_list[0]['attendance_id']}'";
                        $column = 'punch_in';
                        break;
                    case '2':
                        $sql = "SELECT `start_time` FROM `rest_time` WHERE `id` = '{$employee_list[0]['rest_time_id']}'";
                        $column = 'start_time';
                        break;
                    case '3':
                        $sql = "SELECT `end_time` FROM `rest_time` WHERE `attendance_id` = '{$employee_list[0]['attendance_id']}' ORDER BY `end_time` DESC";
                        $column = 'end_time';
                        break;
                    case '4':
                        $sql = "SELECT `punch_out` FROM `attendance` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `punch_out` DESC";
                        $column = 'punch_out';
                        break;
                }
                if($res = $_link->query($sql)){
                    $start_message_list = array();
                    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                        $start_message_list[] = $row;
                    }
                }
                $date = date('Y-m-d H:i');
                $date2 = substr($start_message_list[0][$column],0,16);
                if($date == $date2){
                    $messages = '1分後にまたお願いします。';
                    line_reply($messages,$reply_token,$access_token);
                }else{
                    $package_id = $event['message']['packageId'];
                    $sticker_id = $event['message']['stickerId'];
                    $date = date('Y-m-d H:i:s');
                    if($package_id == '11537'){
                        switch ($sticker_id) {
                            case '52002738':
                            if($employee_list[0]['attendance_status'] == 1 || $employee_list[0]['attendance_status'] == 3){
                                //출근중에 출근 yes&no
                                $template_message = '現在、勤務中です、もう一度出勤しますか？';
                                $template_data = '/1/1';
                                // $group_message = '現在は勤務中です、もう一度出勤しますか？';
                            }else if($employee_list[0]['attendance_status'] == 2){
                                //휴식중에 출근 yes&no
                                $template_message = '現在、休憩中です、出勤しますか？';
                                $template_data = '/2/1';
                                // $group_message = '現在は休憩中です、出勤しますか？';
                            }else if($employee_list[0]['attendance_status'] == 0 || $employee_list[0]['attendance_status'] == 4){
                                //정상적인 출근
                                $group_message = '出勤し';
                                $sql = "INSERT INTO `attendance` (`employee_id`,`punch_in`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                                $status = 1;
                            }
                            break;
                            case '52002745':
                            if($employee_list[0]['attendance_status'] == 2){
                                //휴식중에 휴식
                                $template_message = '現在、休憩中です、もう一度休憩しますか？';
                                $template_data = '/2/2';
                                // $group_message = '現在は休憩中です、もう一度休憩しますか？';
                            }else if($employee_list[0]['attendance_status'] == 4){
                                //퇴근중에 휴식
                                $template_message = '現在、退勤状態です、休憩しますか？';
                                $template_data = '/4/2';
                                // $group_message = '現在は退勤状態です、休憩しますか？';
                            }else if($employee_list[0]['attendance_status'] == 1 || $employee_list[0]['attendance_status'] == 3){
                                //정상적인 휴식
                                $group_message = '休憩入り';
                                $sql = "INSERT INTO `rest_time` (`attendance_id`,`start_time`) VALUES ('{$employee_list[0]['attendance_id']}','{$date}')";
                                $status = 2;
                            }
                            break;
                            case '52002751':
                            if($employee_list[0]['attendance_status'] == 1 || $employee_list[0]['attendance_status'] == 3){
                                //출근중에 휴식끝
                                $template_message = '現在、勤務中です、休憩終了しますか？';
                                $template_data = '/1/3';
                                // $group_message = '現在は勤務中です、休憩終了しますか？';
                            }else if($employee_list[0]['attendance_status'] == 4){
                                //퇴근중에 휴식끝
                                $template_message = '現在、退勤状態です、休憩終了しますか？';
                                $template_data = '/4/3';
                                // $group_message = '現在は退勤状態です、休憩終了しますか？';
                            }else if($employee_list[0]['attendance_status'] == 2){
                                //정상적인 휴식끝
                                $group_message = '休憩終わり';
                                $sql = "UPDATE `rest_time` SET `end_time` = '{$date}' WHERE `id` = '{$employee_list[0]['rest_time_id']}'";
                                $status = 3;
                            }
                            break;
                            case '52002739':
                            if($employee_list[0]['attendance_status'] == 2){
                                //휴식중에 퇴근
                                $template_message = '現在、休憩中です、退勤しますか？';
                                $template_data = '/2/4';
                                // $group_message = '現在は休憩中です、退勤しますか？';
                            }else if($employee_list[0]['attendance_status'] == 4){
                                //퇴근중에 퇴근
                                $template_message = '現在、退勤状態です、退勤しますか？';
                                $template_data = '/4/4';
                                // $group_message = '現在は退勤状態です、退勤しますか？';
                            }else if($employee_list[0]['attendance_status'] == 1 || $employee_list[0]['attendance_status'] == 3){
                                //정상적인 퇴근
                                $group_message = '退勤し';
                                $sql = "UPDATE `attendance` SET `punch_out` = '{$date}' WHERE `id` = '{$employee_list[0]['attendance_id']}'";
                                $status = 4;
                            }
                            break;
                        }
                }
                    //들어온 값이 출퇴근인지 휴식인지 분기한다.
                    if(isset($group_message)){
                        //sql을 실행한다
                        if($res = $_link->query($sql)){
                            //출퇴근 휴식별로 컬럼을 분류한다.
                            if($status == 1 || $status == 4){
                                $employee_column = 'attendance_id';
                            }else if($status == 2 || $status == 3){
                                $employee_column = 'rest_time_id';
                            }
                            //성공적으로 값이 1줄 들어갔다면 그 pk값을 확인한다 insert한정
                            if($status == 1 || $status == 2){
                                $pk_id = $_link->insert_id;
                            }else if($status == 3 || $status == 4){
                                $pk_id = 0;
                            }
                            $sql = "UPDATE `employee` SET `attendance_status` = '{$status}', `{$employee_column}` = '{$pk_id}' WHERE `id` = '{$employee_list[0]['id']}'";
                            if($res = $_link->query($sql)){
                                $group_message2 = 'ました。';
                            }else{
                                $group_message2 = 'に失敗しました。';
                            }
                        }
                        $messages = $employee_list[0]['name'].'さんが'.$group_message.$group_message2;
                        line_reply($messages,$reply_token,$access_token);
                    }else if(isset($template_message)){
                        //템플릿 1회성 사용을 위한 설정 1=사용중
                        $sql = "UPDATE `line_info_group` SET `template_status` = '1' WHERE `id` = '{$group_id[0]['id']}'";
                        if($res = $_link->query($sql)){
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
            // }else{
            //     $messages = '指定のグループでお願いします。';
            //     line_reply($messages,$reply_token,$access_token);
            // }
        }
    }
}
//3차 에러상황 Yes&No 선택
if(isset($event['postback']) && isset($event['source']['userId'])){
    $user_id = $event['source']['userId'];
    $employee_list = employee_info($user_id,$_link);
    $employee_list_id = $employee_list[0]['id'];
    $group_id = line_group_info($employee_list_id,$_link);
    $group_id[0]['template_status'];
    if($group_id[0]['template_status'] == 1){
        //템플릿 1회성 사용을 위한 설정 0=사용끝
        $sql = "UPDATE `line_info_group` SET `template_status` = '0' WHERE `id` = '{$group_id[0]['id']}'";
        if($res = $_link->query($sql)){
        }

        $reply_token = $event['replyToken'];
        //제일처음 에러를 부른사람과 탬플렛 클릭하는 사람이 같아야함
        //0:yes,no 1:user_id 2:현재상태 3:시도항목
        $postback = explode('/',$event['postback']['data']);
        if($event['source']['userId'] == $postback[1]){
            $user_id = $postback[1];
            $employee_list = employee_info($user_id,$_link);
            $date = date('Y-m-d H:i:s');
            if($postback[0] == 'yes'){
                $reply_text = '変更しました。';
                switch ($postback[2]) {
                    case '1':
                        switch ($postback[3]) {
                            case '1':
                                //出勤中に出勤 출근새로 추가후 휴식&출근 정보 덮고  *누락정보 전날 퇴근
                                $reply_error_text = '出勤中に出勤しました。（前日の退勤情報なし）';
                                $sql = "INSERT INTO `attendance` (`employee_id`,`punch_in`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                                if($res = $_link->query($sql)){
                                    $pk_id = $_link->insert_id;
                                    $sql = "UPDATE `employee` SET `attendance_status` = '{$postback[3]}', `attendance_id` = '{$pk_id}', `rest_time_id` = '0' WHERE `id` = '{$employee_list[0]['id']}'";
                                    if($res = $_link->query($sql)){
                                        $msg = '成功しました。';
                                    }
                                }
                                $status = 1;
                                break;
                            case '3':
                                //出勤中に休憩終わり 휴식시작 정보가 없으므로 휴식끝으로 인설트 함 *누락정보 휴식시작
                                $reply_error_text = '出勤中に休憩終わりました。（休憩始め情報なし）';
                                $sql = "INSERT INTO `rest_time` (`attendance_id`, `end_time`) VALUES ('{$employee_list[0]['attendance_id']}', '{$date}')";
                                if($res = $_link->query($sql)){
                                    $pk_id = 0;
                                    $postback[3] = '1';
                                    $sql = "UPDATE `employee` SET `attendance_status` = '{$postback[3]}', `rest_time_id` = '{$pk_id}' WHERE `id` = '{$employee_list[0]['id']}'";
                                    if($res = $_link->query($sql)){
                                        $msg = '成功しました。';
                                    }
                                }
                                $status = 3;
                                break;
                        }
                        break;
                    case '2':
                        switch ($postback[3]) {
                            case '1':
                                //休憩中に出勤 출근정보추가 휴식중에 출근 이전 정보 덮고  *누락정보 휴식끝 퇴근
                                $reply_error_text = '休憩中に出勤しました。（休憩終わり・退勤情報なし）';
                                $sql = "INSERT INTO `attendance` (`employee_id`,`punch_in`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                                if($res = $_link->query($sql)){
                                    $pk_id = $_link->insert_id;
                                    $sql = "UPDATE `employee` SET `attendance_status` = '{$postback[3]}', `attendance_id` = '{$pk_id}', `rest_time_id` = '0' WHERE `id` = '{$employee_list[0]['id']}'";
                                    if($res = $_link->query($sql)){
                                        $msg = '成功しました。';
                                    }
                                }
                                $status = 1;
                                break;
                            case '4':
                                //休憩中に退勤 퇴근을한다 휴식중인 정보를 덮고  *누락정보 휴식끝
                                $reply_error_text = '休憩中に退勤しました。（休憩終わり情報なし）';
                                $sql = "UPDATE `attendance` SET `punch_out` = '{$date}' WHERE `id` = '{$employee_list[0]['attendance_id']}'";
                                if($res = $_link->query($sql)){
                                    $sql = "UPDATE `employee` SET `attendance_status` = '{$postback[3]}', `attendance_id` = '0', `rest_time_id` = '0' WHERE `id` = '{$employee_list[0]['id']}'";
                                    if($res = $_link->query($sql)){
                                        $msg = '成功しました。';
                                    }
                                }
                                $status = 4;
                                break;
                        }
                        break;
                    case '4':
                        $error_date = date('Y-m-d');
                        switch ($postback[3]) {
                            case '2':
                                //退勤中に休憩 그날 기준으로 더미 출근정보를 만들고 휴식을 시작한다 *누락정보 출근
                                //더미 출근 정보 연/월/일  시간정보 없음
                                $reply_error_text = '退勤中に休憩しました。（出勤情報なし）';
                                $sql = "INSERT INTO `attendance` (`employee_id`,`punch_in`) VALUES ('{$employee_list[0]['id']}','{$error_date}')";
                                if($res = $_link->query($sql)){
                                    $pk_attendance_id = $_link->insert_id;
                                    //휴식정보
                                    $sql = "INSERT INTO `rest_time` (`attendance_id`,`start_time`) VALUES ('{$pk_attendance_id}','{$date}')";
                                    if($res = $_link->query($sql)){
                                        $pk_rest_time_id = $_link->insert_id;
                                        $sql = "UPDATE `employee` SET `attendance_status` = '2', `attendance_id` = '{$pk_attendance_id}', `rest_time_id` = '{$pk_rest_time_id}' WHERE `id` = '{$employee_list[0]['id']}'";
                                        if($res = $_link->query($sql)){
                                            $msg = '成功しました。';
                                        }
                                    }
                                }
                                $status = 2;
                                break;
                            case '3':
                                //退勤中に休憩終わり 그날 기준으로 더미 출근정보 만들고 휴식을 끝을한다 *누락정보 출근, 휴식시작
                                $reply_error_text = '退勤中に休憩終わりました。（出勤・休憩始め情報なし）';
                                $sql = "INSERT INTO `attendance` (`employee_id`,`punch_in`) VALUES ('{$employee_list[0]['id']}','{$error_date}')";
                                if($res = $_link->query($sql)){
                                    $pk_attendance_id = $_link->insert_id;
                                    //휴식정보
                                    $sql = "INSERT INTO `rest_time` (`attendance_id`,`end_time`) VALUES ('{$pk_attendance_id}','{$date}')";
                                    if($res = $_link->query($sql)){
                                        $pk_rest_time_id = $_link->insert_id;
                                        $sql = "UPDATE `employee` SET `attendance_status` = '1', `attendance_id` = '{$pk_attendance_id}', `rest_time_id` = '0' WHERE `id` = '{$employee_list[0]['id']}'";
                                        if($res = $_link->query($sql)){
                                            $msg = '成功しました。';
                                        }
                                    }
                                }
                                $status = 3;
                                break;
                        }
                        break;
                }
                //
                // if($postback[2] == 1){
                //     switch ($postback[3]) {
                //         case '1':
                //             //出勤中に出勤
                //             $test = '出勤中に出勤';
                //             $sql = "INSERT INTO `attendance` (`employee_id`,`punch_in`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                //             $status = 1;
                //             break;
                //         case '3':
                //             //出勤中に休憩終わり
                //             $test = '出勤中に休憩終わり';
                //             $sql = "UPDATE `rest_time` SET `end_time` = '{$date}' WHERE `attendance_id` = '{$employee_list[0]['attendance_id']}'";
                //             $status = 3;
                //             break;
                //     }
                // }else if($postback[2] == 2){
                //     switch ($postback[3]) {
                //         case '1':
                //             //休憩中に出勤
                //             $test = '休憩中に出勤';
                //             $sql = "INSERT INTO `attendance` (`employee_id`,`punch_in`) VALUES ('{$employee_list[0]['id']}','{$date}')";
                //             //휴식중에 출근을하면 휴식정보를 덮어 버린다.
                //             // $sql = "UPDATE `rest_time` SET `end_time` = '{$date}' WHERE `attendance_id` = '{$employee_list[0]['attendance_id']}'";
                //             $status = 1;
                //             break;
                //         case '4':
                //             //休憩中に退勤
                //             $test = '休憩中に退勤';
                //             $sql = "UPDATE `attendance` SET `punch_out` = '{$date}' WHERE `id` = '{$employee_list[0]['attendance_id']}'";
                //             //휴식중에 퇴근을하면 휴식정보를 덮어 버린다.
                //             // $sql = "UPDATE `rest_time` SET `end_time` = '{$date}' WHERE `attendance_id` = '{$employee_list[0]['attendance_id']}'";
                //             $status = 4;
                //             break;
                //     }
                // }else if($postback[2] == 4){
                //     switch ($postback[3]) {
                //         case '2':
                //             //退勤中に休憩
                //             $test = '退勤中に休憩';
                //             $sql = "INSERT INTO `rest_time` (`attendance_id`,`start_time`) VALUES ('{$employee_list[0]['attendance_id']}','{$date}')";
                //             //퇴근중에 휴식을하면
                //             $status = 2;
                //             break;
                //         case '3':
                //             //退勤中に休憩終わり
                //             $test = '退勤中に休憩終わり';
                //             $sql = "UPDATE `rest_time` SET `end_time` = '{$date}' WHERE `attendance_id` = '{$employee_list[0]['attendance_id']}'";
                //             $status = 3;
                //             break;
                //     }
                // }
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
                line_reply($message,$reply_token,$access_token);
                //에러시 나에게로 문자를 보냄(임시)
                // for ($i=0; $i < $count; $i++) {
                //     $to = $employee_id_list[$i]['user_id'];
                //     line_push($to,$messages,$access_token);
                // }
                $to = 'Ue6824041a4f68a7ef205494623659b08';
                line_push($to,$messages,$access_token);
            }
        }else{
            // unset($event['postback']);
            $message = array(
                'type' => 'text',
                'text' => 'REPLY world:'.'本人だけ選択できます、もう一度テンプレートを開いてください。'
            );
            line_reply($message,$reply_token,$access_token);
        }
    }
}
 ?>
