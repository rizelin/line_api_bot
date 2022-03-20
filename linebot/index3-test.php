<?php
// 은혜 DB연결 & ウネDB
require_once('./require2/mysql.php');
$access_token = 'L3z1Lzo3B/U2WwL7Rh8t2YGplwDUuXhdUNS17TVe87KrwpV7Kg0m8xoyqdK5UOUcZVirrtpMxfdiMkAKiZvLaQscjmUF6rAXy/dUCaePOJq3Acia4Dmb5Epuo+zGkE/oFur/X51Unild58zAK9N9SQdB04t89/1O/w1cDnyilFU=';
//라인에서 정보를 가져온다 & LINE情報を持ってくる
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);
$event = $receive['events'][0];
$event_type = $event['type'];
//유저 아이디 찾기 & user_id探し
function user_select($user_id){
    global $_link;
    $user_id = $_link->real_escape_string($user_id);
    $sql = "SELECT `user_id` FROM `line_info` WHERE `user_id` = '{$user_id}' LIMIT 0,1" ;
    $list = array();
    if($res = $_link->query($sql)){
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $list[] = $row;
        }
    }
    return $list;
}
//유저 display_name찾기 & user profile情報探し
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
//출퇴근 결과 리플레이 & 出勤退勤結果reply
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
//출퇴근 결과 리플레이 템플릿 & 出勤退勤結果テンプレート
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
//메세지 송신 푸쉬 & メッセージ送信 push
function line_push($to,$messages,$access_token){
    $headers = array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Bearer ' . $access_token
    );
    $message = array(
        'type' => 'text',
        'text' => $messages
    );
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
//메세지 송신 푸쉬 템플릿 & メッセージ送信 push テンプレート
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
//근무자 상태 날짜 구하기 & 就業員の勤怠日付求める
function employee_status($user_status){
    global $_link;
    $user_status = $_link->real_escape_string($user_status);
    $sql="SELECT `id`,`employee_id`,`type`,`status_datetime` FROM `employee_datetime` WHERE `employee_id` = '{$user_status}' ORDER BY `status_datetime` DESC LIMIT 0,1";
    global $_link;
    if($res = $_link->query($sql)){
        $datetime_status = array();
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $datetime_status[] = $row;
        }
    }
    return $datetime_status;
}
//근무자 table정보 구하기 & 就業員の情報も求める
function employee_info($user_id){
    global $_link;
    $user_id = $_link->real_escape_string($user_id);
    $sql ="SELECT `id`,`name`,`department_id`,`resign` FROM `employee` WHERE `id` = (
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
//템플릿에러요소 확인하기 & テンプレートのエラー分岐
function template_errors($date,$now_datetime,$template_id,$disposable_template_id,$disposable_template_status,$reply_token,$access_token){
    //5분 이내로 탬플릿사용 & 5分以内なのか？
    if($date > $now_datetime){
        //마지막 템플릿이 맞는지? & 最後のテンプレとなのか？
        if($disposable_template_id == $template_id){
            //사용된 템플렛인지? & 使用されたテンプレとなのか？
            if($disposable_template_status == 0){
                $template_errors = true;
            }else{
                $messages = 'この選択肢は使用済みです。';
            }
        }else{
            $messages = '一番最新の選択肢のみ使えます。';
        }
    }else{
        $messages = '時間が5分経過して使用できません、またお願いします。';
    }
    line_reply($messages,$reply_token,$access_token);
    return $template_errors;
}
//텍스트 수집
function log_save($user_id,$event_source_type,$event_source_id,$message_text){
    global $_link;
    if($event_source_type == 'group'){
        $user_type = ',`group_id`';
        $user_type_values = ",'".$_link->real_escape_string($event_source_id)."'";
    }else if($event_source_type == 'room'){
        $user_type = ',`room_id`';
        $user_type_values = ",'".$_link->real_escape_string($event_source_id)."'";
    }
    $user_id = $_link->real_escape_string($user_id);
    $text_log = $_link->real_escape_string($message_text);
    $now_datetime = date('Y-m-d H:i:s');
    $now_datetime = $_link->real_escape_string($now_datetime);
    if(isset($user_type)){
        $log_column = "`user_id`,`text_log`,`log_datetime`".$user_type;
        $log_values = "'{$user_id}','{$text_log}','{$now_datetime}'".$user_type_values;
    }else{
        $log_column = "`user_id`,`text_log`,`log_datetime`";
        $log_values = "'{$user_id}','{$text_log}','{$now_datetime}'";
    }
    $sql = "INSERT INTO `line_text_log`({$log_column})VALUES({$log_values})";
    $res = $_link->query($sql);
}
//친구추가 & 友達追加
if($event_type == 'follow'){
    //팔로우 상태는 반드시 ID가 존재함 & follow状態では必ずIDが存在する
    if(isset($event['source']['userId'])){
        $user_id = $event['source']['userId'];
        $list = user_select($user_id,$_link);
    }else{
        //만약 에러가 발생할 경우 & もしエラーが生じた場合
        $messages = 'エラーが生じて登録できませんでした。';
        line_reply($messages,$reply_token,$access_token);
        return;
    }
}else if($event_type == 'message'){
    if($event['source']['type'] == 'user'){
        //1:1대화는 반드시 ID가 존재함 & 1:1会話でも必ずIDが存在する &
        if(isset($event['source']['userId'])){
            $user_id = $event['source']['userId'];
            $list = user_select($user_id,$_link);
        }else{
            //만약 에러가 발생할 경우 & もしエラーが生じた場合
            $messages = 'エラーが生じて登録できませんでした。';
            line_reply($messages,$reply_token,$access_token);
            return;
        }
    }
}
//유저 정보 추가 & user情報追加
if(!empty($list) && $event_type == 'follow'){
    $to = $user_id;
    //이미 등록된 유저인지 확인한다. & 既に登録されたか確認
    if($list[0]['user_id'] == $user_id){
        $messages = '登録済みのユーザーです。';
    }
}else if(empty($list)){
    $to = $user_id;
    $res = display_name($user_id,$access_token);
    $db_user_id = "'".$_link->real_escape_string($user_id)."'";
    $display_name = $res['displayName'];
    unset($res);
    $sql = "INSERT INTO `line_info` (`user_id`,`display_name`) VALUES ({$db_user_id},'{$display_name}')";
    if(!empty($db_user_id) && !empty($display_name)){
        if($res = $_link->query($sql)){
            $messages = '登録ありがとうございます。';
        }else{
            $messages = '登録に失敗しました。';
        }
    }
}
//등록인사　& 登録挨拶
if(isset($to)){
    line_push($to,$messages,$access_token);
    return;
}
//1차 에러처리 & 一次、エラー処理
if($event_type == 'message'){
    //리플라이 토큰 작성 & replyトークン
    $reply_token = $event['replyToken'];
    //그룹이라면 지정그룹인지 아닌지 판별한다. & もしグループなら
    if(isset($event['source']['groupId'])){
        $messages = '申し訳ございません、1：1のチャットのみ対応します。';
        line_reply($messages,$reply_token,$access_token);
        //텍스트 수집
        $user_id = $event['source']['userId'];
        $event_source_type = $event['source']['type'];
        $event_source_id = $event['source']['groupId'];
        $message_text = $event['message']['text'];
        log_save($user_id,$event_source_type,$event_source_id,$message_text);
    //룸이라면 & もしルームなら
    }else if(isset($event['source']['roomId'])){
        $messages = '申し訳ございません、1：1のチャットのみ対応します。';
        line_reply($messages,$reply_token,$access_token);
        //텍스트 수집
        $user_id = $event['source']['userId'];
        $event_source_type = $event['source']['type'];
        $event_source_id = $event['source']['roomId'];
        $message_text = $event['message']['text'];
        log_save($user_id,$event_source_type,$event_source_id,$message_text);
    //개인으로 에러 체크를 한다 & 個人ならエラーをチェックする
    }else if(isset($event['source']['userId'])){
        $user_id = $event['source']['userId'];
        $employee_list = employee_info($user_id);
        //등록유저인지? & 登録userなのか？
        if(isset($employee_list[0]['id']) && isset($employee_list[0]['name']) && isset($employee_list[0]['department_id'])){
            //2차에서 또 사용함
            $now_datetime = date('Y-m-d H:i:s');
            //퇴직유저인가? & 退職userなのか？
            if(empty($employee_list[0]['resign']) || $employee_list[0]['resign'] > $now_datetime){
                //텍스트인지? & テキストなのか?
                if($event['message']['type'] == 'text'){
                    $attendance_start = true;
                    //텍스트 수집
                    $user_id = $event['source']['userId'];
                    $event_source_type = $event['source']['type'];
                    $event_source_id = $event['source']['groupId'];
                    $message_text = $event['message']['text'];
                    log_save($user_id,$event_source_type,$event_source_id,$message_text);
                }else{
                    $messages = '申し訳ございません、勤怠管理のみ対応します。';
                }
            }else{
                $messages = 'IDは存在しますが、使用できない状態です。';
            }
        }else{
            $messages = '申し訳ございません、IDが登録されておりませんので、もう一度友達、登録お願いします。';
        }
        line_reply($messages,$reply_token,$access_token);
    }
}
//2차 출퇴근현황 및 에러검사 & 二次、勤怠処理、エラー検査
if($attendance_start){
    //유저 status구하기 &　userのstatus求める
    $user_status = $employee_list[0]['id'];
    $employee_status = employee_status($user_status);
    $user_work_time = substr($employee_status[0]['status_datetime'],11,5);

    //현재 상태를 묻는다 & 現在の状態を求める
    if($event['message']['text'] == '状態確認'){
        if(isset($employee_status[0]['type'])){
            switch ($employee_status[0]['type']){
                case '1':
                    $status_message = '現在は勤務中です、出勤した時間は「';
                    break;
                case '2':
                    $status_message = '現在は休憩中です、休憩を始めた時間は「';
                    break;
                case '3':
                    $status_message = '現在は勤務中です、休憩が終わった時間は「';
                    break;
                case '4':
                    $status_message = '勤務外です、最後に退勤した時間は「';
                    break;
            }
            $messages = $status_message.$user_work_time.'」です。';
        }else{
            $messages = 'まだ、勤怠記録がありません、これから頑張りましょう！！';
        }
        line_reply($messages,$reply_token,$access_token);
        return;
    }
    //명령이 들어온 순간 가장 마지막 명령시간을 찾아본다 & 命令が入ったら最後の名なのか探してみる
    //1분이내에 같은 명령이 있는지ｘｘｘ 명령이 연속 두 번 오는지 확인한다 & 1分以内にまた命令が入ったか確認する
    if(isset($employee_status[0]['status_datetime'])){
        $date = strtotime($employee_status[0]['status_datetime'].'+1 minutes');
        $date = date('Y-m-d H:i:s', $date);
    }
    //1분 이내거나 신규유저 일 경우　& 1分以内かもしくは新規のuserの場合
    if($now_datetime > $date || !isset($date)){
        if(isset($employee_status[0]['type'])){
            $employee_type = $employee_status[0]['type'];
        }
        switch ($event['message']['text']) {
            //출근　& 出勤
            case '出勤':
                //각 분기별 신규유저인지 확인한다.
                if(isset($date)){
                    switch ($employee_type) {
                        case '1':
                            //출근중에 출근 yes&no
                            $template_message = '現在、勤務中です、もう一度出勤しますか？';
                            $template_data = '/1/1';
                            break;
                        case '2':
                            //휴식중에 출근 yes&no
                            $template_message = '現在、休憩中です、出勤しますか？';
                            $template_data = '/2/1';
                            break;
                        case '3':
                            //출근중에 출근 yes&no
                            $template_message = '現在、勤務中です、もう一度出勤しますか？';
                            $template_data = '/1/1';
                            break;
                        case '4':
                            $output_message = '出勤しました。';
                            $datetime_status = 1;
                            break;
                    }
                }else{
                    $output_message = '出勤しました。';
                    $datetime_status = 1;
                }
                break;
            //휴식시작 & 休入
            case '休入':
                //각 분기별 신규유저인지 확인한다.
                if(isset($date)){
                    switch ($employee_type) {
                        case '2':
                            //휴식중에 휴식
                            $template_message = '現在、休憩中です、もう一度休憩しますか？';
                            $template_data = '/2/2';
                            break;
                        case '4':
                            //퇴근중에 휴식
                            $template_message = '現在、退勤状態です、休憩しますか？';
                            $template_data = '/4/2';
                            break;
                        case '1':
                            //정상적인 휴식
                            $output_message = '休憩入りました。';
                            $datetime_status = 2;
                            break;
                        case '3':
                            //정상적인 휴식
                            $output_message = '休憩入りました。';
                            $datetime_status = 2;
                            break;
                    }
                }else{
                    $messages = '新規の方は出勤からお願いします。';
                }
                break;
            //휴식끝　& 休戻
            case '休戻':
                //각 분기별 신규유저인지 확인한다.
                if(isset($date)){
                    switch ($employee_type) {
                        case '1':
                            //출근중에 휴식끝
                            $template_message = '現在、勤務中です、休憩終了しますか？';
                            $template_data = '/1/3';
                            break;
                        case '3':
                            //출근중에 휴식끝
                            $template_message = '現在、勤務中です、休憩終了しますか？';
                            $template_data = '/1/3';
                            break;
                        case '4':
                            //퇴근중에 휴식끝
                            $template_message = '現在、退勤状態です、休憩終了しますか？';
                            $template_data = '/4/3';
                            break;
                        case '2':
                            //정상적인 휴식끝
                            $output_message = '休憩終わりました。';
                            $datetime_status = 3;
                            break;
                    }
                }else{
                    $messages = '新規の方は出勤からお願いします。';
                }
                break;
            //퇴근 & 退勤
            case '退勤':
                //각 분기별 신규유저인지 확인한다.
                if(isset($date)){
                    switch ($employee_type) {
                        case '2':
                            //휴식중에 퇴근
                            $template_message = '現在、休憩中です、退勤しますか？';
                            $template_data = '/2/4';
                            break;
                        case '4':
                            //퇴근중에 퇴근
                            $template_message = '現在、退勤状態です、退勤しますか？';
                            $template_data = '/4/4';
                            break;
                        case '1':
                            //정상적인 퇴근
                            $output_message = '退勤しました。';
                            $datetime_status = 4;
                            break;
                        case '3':
                            //정상적인 퇴근
                            $output_message = '退勤しました。';
                            $datetime_status = 4;
                            break;
                    }
                }else{
                    $messages = '新規の方は出勤からお願いします。';
                }
                break;
        }
        //들어온 값을 insert한다 & 入った値をinsertする
        if(isset($output_message)){
            //정사적으로 들어가는데 이전에 활성화중인 템플이 있다면? 비활성화 시킨다. & 実行する前に以前活性化されている、テンプレートを閉める
            $sql = "SELECT `id`,`status` FROM `disposable_template` WHERE `employee_id` = '{$employee_list[0]['id']}' ORDER BY `id` DESC LIMIT 0,1";
            if($res = $_link->query($sql)){
                if($res->num_rows == 1){
                    if($row = $res->fetch_array(MYSQLI_ASSOC)){
                        $template_status = $row;
                    }
                    if(isset($template_status)){
                        $sql = "UPDATE `disposable_template` SET `status` = 1 WHERE `id` = '{$template_status['id']}'";
                        $res = $_link->query($sql);
                    }
                }
            }
            //sql을 실행한다　& sqlを実行する
            $sql = "INSERT INTO `employee_datetime` (`employee_id`,`type`,`status_datetime`) VALUES ('{$employee_list[0]['id']}','{$datetime_status}','{$now_datetime}')";
            $res = $_link->query($sql);
            $messages = $employee_list[0]['name'].'さん'.$output_message;
        }else if(isset($template_message)){
            //id 유저id 템플릿id 유효기한 사용여부 만든날짜　& 1回用のエラーお知らせテンプレートを作る
            $sql = "INSERT INTO `disposable_template` (`employee_id`,`status`,`make_datetime`) VALUES ('{$employee_list[0]['id']}','0','{$now_datetime}')";
            if($res=$_link->query($sql)){
                $template_id = $_link->insert_id;
            }
            $template = array(
                'type' => 'confirm',
                'text' => $employee_list[0]['name'].'さんは'.$template_message,
                'actions' => array(
                    //여기에 메세지 보낼 1주소 (리플레이,푸쉬) 보낼 정보 2예스&노 3어떤정보인지
                    //テンプレートの値 1.テンプレートID、2.yes&no 3.user_id 4.出勤退勤分岐データ
                    array('type' => 'postback', 'label' => 'はい', 'data' => $template_id.'/yes/'.$user_id.$template_data),
                    array('type' => 'postback', 'label' => 'いいえ', 'data' => $template_id.'/no/'.$user_id.'/'.$sql)
                )
            );
            $message = array(
                'type'    => 'template',
                'altText' => 'モバイルデバイスで確認できます',
                'template' => $template
            );
            line_reply_template($message,$reply_token,$access_token);
        }
    }else{
        $messages = '1分後にまたお願いします';
    }
    line_reply($messages,$reply_token,$access_token);
}
//3차 에러상황 Yes&No 선택 & 三次、エラーテンプレートYes・No
if(isset($event['postback']) && isset($event['source']['userId'])){
    //유저ID 직원번호 구하기 & userの従業人numberを求める
    $reply_token = $event['replyToken'];
    $user_id = $event['source']['userId'];
    $employee_list = employee_info($user_id);
    if(isset($employee_list[0]['id']) && isset($employee_list[0]['name']) && isset($employee_list[0]['department_id'])){
        $employee_list_id = $employee_list[0]['id'];
        //1회용 탬플릿 정보 구하기 & 1回用のテンプレートメッセージの情報を求める
        $sql = "SELECT `id`,`status`,`make_datetime` FROM `disposable_template` WHERE `employee_id` = '{$employee_list_id}' ORDER BY `make_datetime` DESC LIMIT 0,1";
        if($res=$_link->query($sql)){
            $disposable_template = array();
            while($row = $res->fetch_array(MYSQLI_ASSOC)){
                $disposable_template[] = $row;
            }
        }
        if(isset($disposable_template[0]['id']) && isset($disposable_template[0]['status']) && isset($disposable_template[0]['make_datetime'])){
            $disposable_template_id = $disposable_template[0]['id'];
            $disposable_template_status = $disposable_template[0]['status'];
            $date = strtotime($disposable_template[0]['make_datetime'].'+5 minutes');
            $date = date('Y-m-d H:i:s', $date);
            $now_datetime = date('Y-m-d H:i:s');
            //제일처음 에러를 부른사람과 탬플렛 클릭하는 사람이 같아야함
            //0:yes,no 1:user_id 2:현재상태 3:시도항목
            $postback = explode('/',$event['postback']['data']);
            if($event['source']['userId'] == $postback[2]){
                $user_id = $postback[2];
                $template_id = $postback[0];
                if($postback[1] == 'yes'){
                    $template_errors = template_errors($date,$now_datetime,$template_id,$disposable_template_id,$disposable_template_status,$reply_token,$access_token);
                    if($template_errors){
                        $sql = "UPDATE `disposable_template` SET `status` = 1 WHERE `id` = '{$disposable_template_id}'";
                        if($res = $_link->query($sql)){
                            switch ($postback[3]) {
                                case '1':
                                    switch ($postback[4]) {
                                        case '1':
                                            //出勤中に出勤 출근새로 추가후 휴식&출근 정보 덮고  *누락정보 전날 퇴근
                                            $employee_type = 1;
                                            $reply_error_text = '出勤中に出勤しました。（前日の退勤情報なし）';
                                            break;
                                        case '3':
                                            //出勤中に休憩終わり 휴식시작 정보가 없으므로 휴식끝으로 인설트 함 *누락정보 휴식시작
                                            $employee_type = 3;
                                            $reply_error_text = '出勤中に休憩終わりました。（休憩始め情報なし）';
                                            break;
                                    }
                                    break;
                                case '2':
                                    switch ($postback[4]) {
                                        case '1':
                                            //休憩中に出勤 출근정보추가 휴식중에 출근 이전 정보 덮고  *누락정보 휴식끝 퇴근
                                            $employee_type = 1;
                                            $reply_error_text = '休憩中に出勤しました。（休憩終わり・退勤情報なし）';
                                            break;
                                        case '4':
                                            //休憩中に退勤 퇴근을한다 휴식중인 정보를 덮고  *누락정보 휴식끝
                                            $employee_type = 4;
                                            $reply_error_text = '休憩中に退勤しました。（休憩終わり情報なし）';
                                            break;
                                        case '2':
                                            //休憩中に休憩します。휴식중에 휴식을 합니다. *누락정보 휴식끝
                                            $employee_type = 2;
                                            $reply_error_text = '休憩中に休憩しました。（休憩終わり情報なし）';
                                            break;
                                    }
                                    break;
                                case '4':
                                    switch ($postback[4]) {
                                        case '2':
                                            //退勤中に休憩
                                            $employee_type = 2;
                                            $reply_error_text = '退勤中に休憩しました。（出勤情報なし）';
                                            break;
                                        case '3':
                                            //退勤中に休憩終わり 그날 기준으로 더미 출근정보 만들고 휴식을 끝을한다 *누락정보 출근, 휴식시작
                                            $employee_type = 3;
                                            $reply_error_text = '退勤中に休憩終わりました。（出勤・休憩始め情報なし）';
                                            break;
                                        case '4':
                                            //退勤中に退勤しました
                                            $employee_type = 4;
                                            $reply_error_text = '退勤中に退勤しました。（出勤情報なし）';
                                            break;
                                    }
                                    break;
                            }
                            $sql = "INSERT INTO `employee_datetime` (`employee_id`,`type`,`status_datetime`) VALUES ('{$employee_list[0]['id']}','{$employee_type}','{$disposable_template[0]['make_datetime']}')";
                            if(!$res = $_link->query($sql)){
                                $reply_error_text = "失敗しました。";
                            }
                            $messages = $reply_error_text;
                        }
                    }
                }else if($postback[1] == 'no'){
                    $template_errors = template_errors($date,$now_datetime,$template_id,$disposable_template_id,$disposable_template_status,$reply_token,$access_token);
                    if($template_errors){
                        $sql = "UPDATE `disposable_template` SET `status` = 1 WHERE `id` = '{$disposable_template_id}'";
                        if($res = $_link->query($sql)){
                            $messages = 'いいえを選択しました。';
                        }
                    }
                }
            }
        }
        //에러시 관리자들에게 메세지를 보냄 & エラーの場合管理者達にメッセージを送る
        if(isset($reply_error_text)){
            //관리자 유저Id를 구할 회사 아이디를 구하는 쿼리 &　従業人IDを求める
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
            return;
        }
    }else{
        $messages = '申し訳ございません、IDが登録されておりませんので、もう一度友たち登録お願いします。';
    }
    line_reply($messages,$reply_token,$access_token);
}
 ?>
