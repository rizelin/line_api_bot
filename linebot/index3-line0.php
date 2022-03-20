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
//근무자 table정보 구하기
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
    $sql = "INSERT INTO `line_info` (`user_id`,`display_name`) VALUES ({$db_user_id},'{$display_name}')";
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
    //리플라이 토큰 작성
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
        $user_list = user_select($user_id);
        $employee_list = employee_info($user_id);
        // 퇴직 예정일 이후부터 안되게 바꾸기
        $now_date = date('Y-m-d H:i:s');
        if(empty($employee_list[0]['resign']) || $employee_list[0]['resign'] > $now_date){
            $attendance_start = true;
            if(isset($event['message']['packageId'])){
                $package_id = $event['message']['packageId'];
                $sticker_id = $event['message']['stickerId'];
                //라인 곰돌이가 ?? 하는 스티커
                if($package_id == 11537 && $sticker_id == 52002744){
                    $user_status = $employee_list[0]['id'];
                    $employee_status = employee_status($user_status);
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
                    $messages = $status_message.$employee_status[0]['status_datetime'].'」です。';
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
            $messages = 'IDは存在しますが、使用できない状態です。';
            line_reply($messages,$reply_token,$access_token);
        }
    }
}
//2차 출퇴근현황 및 에러검사
if($attendance_start && isset($event['message']['packageId'])){
    //유저ID 직원번호 구하기
    $user_id = $event['source']['userId'];
    $employee_list = employee_info($user_id);
    $user_status = $employee_list[0]['id'];
    $employee_status = employee_status($user_status);
    $employee_type = $employee_status[0]['type'];
    $employee_list_id = $employee_list[0]['id'];
    //명령이 들어온 순간 가장 마지막 명령시간을 찾아본다
    //1분이내에 같은 명령이 있는지ｘｘｘ 명령이 연속 두 번 오는지 확인한다
    $date = strtotime($employee_status[0]['status_datetime'].'+1 minutes');
    $date = date('Y-m-d H:i:s', $date);
    $now_datetime = date('Y-m-d H:i:s');
    $flase = false;
    if($flase){
        $messages = ' 1分後にまたお願いします';
        line_reply($messages,$reply_token,$access_token);
    }else{
        $package_id = $event['message']['packageId'];
        $sticker_id = $event['message']['stickerId'];
        $date = date('Y-m-d H:i:s');
        if($package_id == '11537'){
            switch ($sticker_id) {
                //출근
                case '52002738':
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
                        break;
                //휴식시작
                case '52002745':
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
                        break;
                //휴식끝
                case '52002751':
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
                        break;
                //퇴근
                case '52002739':
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
                break;
            }
        }
        //들어온 값이 출퇴근인지 휴식인지 분기한다.
        if(isset($output_message)){
            //sql을 실행한다
            $sql = "INSERT INTO `employee_datetime` (`employee_id`,`type`,`status_datetime`) VALUES ('{$employee_list[0]['id']}','{$datetime_status}','{$now_datetime}')";
            $res = $_link->query($sql);
            $messages = $employee_list[0]['name'].'さん'.$output_message;
            line_reply($messages,$reply_token,$access_token);
        }else if(isset($template_message)){
            //id 유저id 템플릿id 유효기한 사용여부 만든날짜
            $sql = "INSERT INTO `disposable_template` (`employee_id`,`status`,`make_datetime`) VALUES ('{$employee_list[0]['id']}','0','{$now_datetime}')";
            if($res=$_link->query($sql)){
                $template_id = $_link->insert_id;
            }
            $template = array(
                'type' => 'confirm',
                'text' => $employee_list[0]['name'].'さんは'.$template_message,
                'actions' => array(
                    //여기에 메세지 보낼 1주소 (리플레이,푸쉬) 보낼 정보 2예스&노 3어떤정보인지
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
    }
}
//3차 에러상황 Yes&No 선택
if(isset($event['postback']) && isset($event['source']['userId'])){
    //유저ID 직원번호 구하기
    $user_id = $event['source']['userId'];
    $employee_list = employee_info($user_id);
    $employee_list_id = $employee_list[0]['id'];
    $reply_token = $event['replyToken'];
    //1회용 탬플릿 정보 구하기
    $sql = "SELECT `id`,`status`,`make_datetime` FROM `disposable_template` WHERE `employee_id` = '{$employee_list_id}' ORDER BY `make_datetime` DESC LIMIT 0,1";
    if($res=$_link->query($sql)){
        $disposable_template = array();
        while($row = $res->fetch_array(MYSQLI_ASSOC)){
            $disposable_template[] = $row;
        }
    }
    $date = strtotime($disposable_template[0]['make_datetime'].'+5 minutes');
    $date = date('Y-m-d H:i:s', $date);
    $now_datetime = date('Y-m-d H:i:s');
    //제일처음 에러를 부른사람과 탬플렛 클릭하는 사람이 같아야함
    //0:yes,no 1:user_id 2:현재상태 3:시도항목
    $postback = explode('/',$event['postback']['data']);
    if($event['source']['userId'] == $postback[2]){
        $user_id = $postback[2];
        if($postback[1] == 'yes'){
            //5분 이내로 탬플릿사용
            if($date > $now_datetime){
                //마지막 템플릿이 맞는지?
                if($disposable_template[0]['id'] == $postback[0]){
                    //사용된 템플렛인지?
                    if($disposable_template[0]['status'] == 0){
                        $sql = "UPDATE `disposable_template` SET `status` = 1 WHERE `id` = '{$disposable_template[0]['id']}'";
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
                                            //ver2)휴식정보 개선후 가짜 출근정보 없이 다이렉트로 휴식정보를 입력하게 바꿈
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
                    }else{
                        $messages = '使用済みテンプレートです。';
                    }
                }else{
                    $messages = '一番最新のテンプレートのみ使えます。';
                }
            }else{
                $messages = '時間が5分経過して使用できません、またお願いします。';
            }
            line_reply($messages,$reply_token,$access_token);
        }else if($postback[1] == 'no'){
            //5분 이내로 탬플릿사용
            if($date > $now_datetime){
                //마지막 템플릿이 맞는지?
                if($disposable_template[0]['id'] == $postback[0]){
                    //사용된 템플렛인지?
                    if($disposable_template[0]['status'] == 0){
                        $sql = "UPDATE `disposable_template` SET `status` = 1 WHERE `id` = '{$disposable_template[0]['id']}'";
                        if($res = $_link->query($sql)){
                            $messages = 'いいえを選択しました。';
                        }
                    }else{
                        $messages = '使用済みテンプレートです。';
                    }
                }else{
                    $messages = '一番最新のテンプレートのみ使えます。';
                }
            }else{
                $messages = '時間が5分経過して使用できません、またお願いします。';
            }
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
 ?>
