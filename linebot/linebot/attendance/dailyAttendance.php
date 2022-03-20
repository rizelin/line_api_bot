<?php
//ini_set("display_errors", 1);
require_once('../require/mysql.php');
require_once('../require/login.php');
/*data일별로 회사,부서,직원검색*/
//1.会社
$sql = "SELECT * FROM `company` WHERE `status`=0";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $company[] = $row;
    }
}
//2-1.部署
$sql ="SELECT a.`id`,b.`id` as company_id,a.`department_name` FROM `department` as a
       JOIN `company` as b
       ON a.`company_id` = b.`id`
       WHERE a.`status`=0";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $department[] = $row;
    }
}
//2-2.店舗
$sql ="SELECT a.`id`,b.`id` as company_id, a.`store_name` FROM `store` as a
       JOIN `company` as b ON a.`company_id` = b.`id` WHERE a.`status` = 0";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $store[] = $row;
    }
}
//3.雇用形態
$sql ="SELECT * FROM `employment` WHERE `status`=0";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $employment[] = $row;
    }
}

$date = date("Y/m/d");
/*まず情報全部読んでテーブルに作り*/
if (isset($_POST['search'])) {
  $search = $_POST['search'];
  if (!empty($search['company_id'])) {
    $where[] = " (e.`company_id`='{$search['company_id']}' OR d.`company_id`='{$search['company_id']}')";
  }
  if(!empty($search['employment_id'])){
    $where[] = " c.`id`='{$search['employment_id']}'";
  }
  if (isset($where)) {
    $where = 'AND'.implode(" AND ",$where);
  }
  if(!empty($search['date'])){
    $date = $search['date'];
  }
}

//4.従業員
$sql="SELECT a.`id` , a.`name` , a.`store_id` , a.`department_id` , a.`employment_id`
      FROM  `employee` AS a
      LEFT JOIN  `employment` AS c ON a.`employment_id` = c.`id`
      LEFT JOIN  `department` AS d ON a.`department_id` = d.`id`
      LEFT JOIN  `store` AS e ON a.`store_id` = e.`id`
      LEFT JOIN  `company` f ON e.`company_id` = f.`id`
      AND d.`company_id` = f.`id`
      WHERE a.`status` = 0 AND (a.`resign` >='{$date}' OR a.`resign` IS NULL) {$where}";
if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    $employees[] = $row;
  }
}

//5.一日の制限でdatetime取得
$sql="SELECT a.`id`,a.`employee_id`,a.`type`,a.`status_datetime`
      FROM `employee_datetime` a
      LEFT JOIN `employee` b ON b.`id` = a.`employee_id`
      LEFT JOIN `employment` c ON b.`employment_id` = c.`id`
      LEFT JOIN `department` d ON b.`department_id` = d.`id`
      LEFT JOIN `store` e ON b.`store_id` = e.`id`
      LEFT JOIN `company` f ON e.`company_id` = f.`id`AND d.`company_id` = f.`id`
      WHERE DATE(a.`status_datetime`) = '{$date}'
      AND a.`status`=0  {$where}
      ORDER BY a.`status_datetime` ASC";

if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
      $attendance[$row['employee_id']][] = $row;
  }
}

//次の日に連結するものがあるか探す
$sql = "SELECT (CASE WHEN a.`type` !=4
                     THEN a.`employee_id`
                     END) AS employee_id
        FROM  `employee_datetime` AS a, (SELECT  `employee_id` ,  `type` , MAX(`status_datetime`) AS max_time
                                          FROM  `employee_datetime`
                                          WHERE DATE(`status_datetime`) = '{$date}'
                                          GROUP BY  `employee_id`
                                        ) AS max_date
        WHERE a.`employee_id` = max_date.`employee_id`
        AND a.`status_datetime` > max_date.`max_time`
        GROUP BY a.`employee_id`
        ORDER BY a.`status_datetime` ASC";

if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    if (!is_null($row['employee_id'])) {
      $aftDataId[] = $row;
    }
  }
}

//上から保存したIDで、一番早い退勤とmax期限の間
foreach ($aftDataId as $key => $value) {
  $sql = "SELECT a.`id`,a.`employee_id`,a.`type`,a.`status_datetime`
          FROM `employee_datetime` AS a,(SELECT  MIN(`status_datetime`) as min_time
                                         FROM  `employee_datetime`
                                         WHERE `type`=4 AND `status`=0
                                         AND `status_datetime` > '{$date} 23:59:59'
                                         AND `employee_id`='{$value["employee_id"]}') as b
          WHERE a.`status_datetime` <= b.min_time
          AND a.`status_datetime` > '{$date} 23:59:59'
          AND `employee_id`='{$value["employee_id"]}'
          ORDER BY a.`status_datetime` ASC";
  if ($res= $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        if ($row['type'] == 1) {
            unset($aftMonthData[$row['employee_id']]);
            break;
        }else {
            $aftMonthData[$row['employee_id']][] = $row;
        }
    }
  }
}

foreach ($aftMonthData as $key => $value) {
  $attendance[$key] = array_merge($attendance[$key],$aftMonthData[$key]);
}
//echo var_dump($attendance);

/*期限の中の情報のエラー($errors) エラーページに移動を知らせるとき使うin_array(employee_id,$errors) tr
エラーじゃない時間情報はnormal配列:attendance,rest*/
foreach ($attendance as $employeeId => $array) {
  for ($i=0; $i < count($array); $i++) {
        switch ($array[$i]['type']) {
          case 1: if(isset($array[$i-1]['type'])&&$array[$i-1]['type']==3||$array[$i+1]['type']==1) {
                    $errors[] = $employeeId;
                  }else {
                    if (!isset($array[$i+1]['type'])) {
                      $errors[] = $employeeId;
                    }
                    $normalAttendance[$employeeId][] =$array[$i];
                  }
            break;
          case 2: if($array[$i-1]['type']==4||$array[$i+1]['type']!=3) {
                    $errors[] = $employeeId;
                  }else {
                    $normalRest[$employeeId][] =$array[$i];
                  }
            break;
          case 3:if($array[$i+1]['type']==1||$array[$i+1]['type']==3||$array[$i-1]['type']!=2){
                      $errors[] = $employeeId;
                      ($array[$i-1]['type']==2)? $normalRest[$employeeId][] =$array[$i]:'';
                  }else {
                      $normalRest[$employeeId][] =$array[$i];
                  }
            break;
          case 4:if(!isset($array[$i-1]['type']) || $array[$i-1]['type']==4 || $array[$i-1]['type']==2) {
                    $errors[] = $employeeId;
                }else {
                   $normalAttendance[$employeeId][] =$array[$i];
                }
            break;
        }
  }
}

$errorCount = array_count_values($errors);
echo var_dump($errorCount);

foreach ($employees as $key => $array) {
//  echo $array['id']."-><br>";
  for ($i=0; $i < count($normalAttendance[$array['id']]); $i++) {
    if ($normalAttendance[$array['id']][$i]['type'] == 1&&$normalAttendance[$array['id']][$i+1]['type'] == 4) {
        $punchIn = strtotime($normalAttendance[$array['id']][$i]['status_datetime']." GMT");
        $punchOut = strtotime($normalAttendance[$array['id']][$i+1]['status_datetime']);
        $someTime= $punchOut-$punchIn;
        $h = date('H',$someTime);
        $m = floor(date('i',$someTime)/60*100);
        $workTime = "$h.$m";
        $totalTimes['work'][$array['id']] += $workTime;
    }
  }
  for ($i=0; $i < count($normalRest[$array['id']]); $i++) {
    if ($normalRest[$array['id']][$i]['type'] ==2 && $normalRest[$array['id']][$i+1]['type'] ==3) {
        $punchIn = strtotime($normalRest[$array['id']][$i]['status_datetime']. "GMT");
        $punchOut = strtotime($normalRest[$array['id']][$i+1]['status_datetime']);
        $someTime= $punchOut-$punchIn;
        $h = date('H',$someTime);
        $m = floor(date('i',$someTime)/60*100);
        $restTime = "$h.$m";
        $totalTimes['rest'][$array['id']] += $restTime;
    }
  }
}

require_once("./dailyAttendance.tpl.php");
?>
