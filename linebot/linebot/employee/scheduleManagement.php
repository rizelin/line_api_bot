<?php
require_once('../require/mysql.php');
require_once('../require/login.php');

/*必須：勤務時間,退勤時間,曜日
勤務時間が8時間過ぎたら休憩決定必
status:1は削除
*/
$days = array('月','火','水','木','金','土','日');

//1.DBからスケジュール情報
if (isset($_GET['id'])) {
  $schedule['employee_id'] = $_GET['id'];
  $sql = "SELECT `id`,`employee_id`,`start_date`,`end_date`,`week`,`start_time`,`end_time`,`rest_start_time`,`rest_end_time`,`office_hours`
          FROM `line_schedule`
          WHERE `employee_id` = {$schedule['employee_id']} AND `status`= 0
          ORDER BY `id` DESC";
  if ($res =  $_link->query($sql)) {
      while($row = $res->fetch_array(MYSQLI_ASSOC)) {
          $schedules[] = $row;
      }
  }

//勤務曜日を文字で変換
  for ($i=0; $i < count($schedules); $i++) {
    switch ($schedules[$i]['week']) {
      case '1111100': $week[$i] = "平日";
        break;
      case '0000011': $week[$i] = "週末";
          // code...
        break;
      default:  for ($j=0; $j <= 6; $j++) {
                  if (substr( $schedules[$i]['week'], $j ,1) == 1) {
                     $workingDay[] = $days[$j];
                  }
                }
                $week[$i] = implode('/',$workingDay);
                $workingDay ='';
        break;
    }
  }
}

if (isset($_POST['schedule'])) {
    $schedule = $_POST['schedule'];
    $allowColumns = array('id' => 'ID'
                          ,'employee_id'=> '社員コード'
                          ,'start_date' => '適用日付'
                          ,'end_date'   => '適用終わり日付'
                          ,'days'       => '勤務曜日'
                          ,'start_time' => '出勤時間'
                          ,'end_time'   => '退勤時間'
                          ,'rest_start_time'=> '休始時間'
                          ,'rest_end_time'  => '休終時間'
                          ,'office_hours'   => '所定時間'
                          ,'date_error' => '勤務日付'
                          ,'time_error' => '勤務時間'
                          ,'rest_error' => '休憩時間'
                          );
    $requireInputs = array('employee_id','start_time','end_time');
    $error = array(1 => 'が未入力です', 2 => 'が不正な入力です',3=>'を確認してください',4=>'と休終時間は一緒に設定してください',5=>'8時間以上は休憩設定が必ず必要です');

    echo var_dump($schedule);

    foreach ($requireInputs as $key => $value) {
      if (empty($schedule[$value])) {
          $errors[$value] = 1;
      }
    }
    //曜日
    for ($i=1; $i-1 < 7; $i++) {
      if(isset($schedule['days'][$i])){  $week[] = 1; }
      else { $week[] = 0; }
    }
     $week = implode("",$week);
     if ($week == '000000') {
       $errors['days'] = 1;
     }

    if ((!empty($schedule['rest_start_time'])&&empty($schedule['rest_end_time'])) || (!empty($schedule['rest_end_time'])&&empty($schedule['rest_start_time']))) {
        $errors['rest_start_time'] = 4;
    }

    foreach ($schedule as $key => $value) {
      if (!in_array($key,array_keys($allowColumns))) {
          $errors[$key] = 2;
      }
    }

    //適用日付 < 適用終わり日付
    if (!empty($schedule['start_date']) && !empty($schedule['end_date'])) {
        if ($schedule['start_date'] > $schedule['end_date']) {
          $errors['date_error'] = 3;
        }
    }

    //出勤時間 < 退勤時間
      if ($schedule['start_time'] >= $schedule['end_time']) {
          $errors['time_error'] = 3;
      }

    //所定時間
    $schedule['office_hours'] = date('H:i:s',strtotime($schedule['end_time']) - strtotime($schedule['start_time']."GMT"));

    //8時間以上勤務は休憩必
    if ($schedule['office_hours'] >= '09:00:00' && empty($schedule['rest_start_time']) || empty($schedule['rest_end_time'])) {
          $errors['time_error'] = 5;
    }

    //FALSE 1.出勤時間 > 休始時間 2.休始時間 >= 休始時間　3.休始時間 >= 退勤時間　4.休終時間 > 退勤時間
    if ((!empty($schedule['rest_start_time']) && !empty($schedule['rest_end_time']))
          &&($schedule['start_time'] > $schedule['rest_start_time']
          || $schedule['rest_start_time'] >= $schedule['rest_end_time']
          || $schedule['rest_start_time'] >= $schedule['end_time']
          || $schedule['rest_end_time'] > $schedule['end_time'])) {
          $errors['rest_error'] = 3;
    }


    if (isset($errors)) {
        foreach ($errors as $key => $value) {
          $errorMsgs[$key] = "$allowColumns[$key]$error[$value]";
        }
    }else {
        $slqFlg = FALSE;
        $columns = array();
        $values = array();
        foreach ($allowColumns as $key => $value) {
          if (isset($schedule[$key]) && $key !='days' && $key !='id') {
              $columns[] = "`$key`";
              if (empty($schedule[$key])) {
                $values[] = "NULL";
              }else {
                $values[] = "'".$_link->real_escape_string($schedule[$key])."'";
              }
          }
        }

        if (empty($schedule['id'])) {
            //insert
            $columns = implode(",",$columns);
            $values = implode(",",$values);
            $sql = "INSERT INTO `line_schedule`($columns,`week`) VALUES($values,'{$week}')";
            echo $sql;
            if ($res = $_link->query($sql)) {
                  $slqFlg = TRUE;
            }

        }else {
            //update
            foreach ($columns as $key => $value) {
              if ($value != "`id`" && $value != "`employee_id`") {
                  $updateSet[] = "$value={$values[$key]}";
              }
            }
            $updateSet = implode(",",$updateSet);
            $sql = "UPDATE `line_schedule` SET $updateSet,`week`='{$week}' WHERE `id` = {$schedule['id']}";
            if ($res = $_link->query($sql)) {
              if ($_link->affected_rows == 1) {
                  $sqlFlg = TRUE;
              }
            }
        }

        if ($sqlFlg) {
          $_link->commit();
        }else {
          $_link->rollback();
        }
        header("location:./scheduleManagement.php?id=".$schedule['employee_id']);
        exit();
  }
}

require_once("./scheduleManagement.tpl.php");
?>
