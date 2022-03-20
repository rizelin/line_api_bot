<?php
require_once("../require/mysql.php");
require_once('../require/login.php');
//1.会社
$sql = "SELECT * FROM `company` WHERE `status`=0";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $company[] = $row;
    }
}
//2.雇用形態
$sql ="SELECT * FROM `employment` WHERE `status`=0";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $employment[] = $row;
    }
}
//3.従業員情報
$sql="SELECT a.`id`,a.`name`,d.`company_name`,e.`employment_type`
      FROM `employee` as a
      LEFT JOIN `department` as b
      ON b.`id` = a.`department_id`
      LEFT JOIN `store` as c
      ON c.`id` = a.`store_id`
      LEFT JOIN `company` as d
      ON d.`id` = b.`company_id` OR d.`id` = c.`company_id`
      LEFT JOIN `employment` as e
      ON e.`id` = a.`employment_id`
      ";
if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    $employee[$row['id']] = $row;
  }
}

//検索
if(intval(date("d") > 20)){ $minDate = date("Y/m/21"); }
else{ $minDate = date("Y/m/21",strtotime("-1 month")); }
if (isset($_GET['date'])) {$date = $_GET['date'];
  if (substr($date,8,2) > 20) { $minDate = date("Y/m/21",strtotime($_GET['date'])); }
  else { $minDate = date("Y/m/21",strtotime($_GET['date']." -1 month")); }
}
$maxDate = date("Y/m/20",strtotime("{$minDate} +1 month")); //마지막날짜의 가장 늦은 시간(sql에서 date사용하지 않고)

if (isset($_POST['search'])) {
  $search = $_POST['search'];

  if (!empty($search['date'])) {
    $maxDate=''; $minDate='';
    $minDate = date($search['date']."/21");
    $maxDate = date("Y/m/20",strtotime($minDate." +1 month"));
  }
  if (!empty($search['company_id'])) {
    $where[] = "(d.`company_id`='{$search['company_id']}' OR e.`company_id`='{$search['company_id']}')";
  }
  if(!empty($search['employment_id'])){
    $where[] = "b.`employment_id`='{$search['employment_id']}'";
  }
  if (isset($where)) {
    $where = " AND ".implode(" AND ",$where);
  }
}

$sql="SELECT a.`id`,a.`employee_id`,a.`type`,a.`status_datetime`
      FROM `employee_datetime` a
      LEFT JOIN `employee` b ON b.`id` = a.`employee_id`
      LEFT JOIN `employment` c ON b.`employment_id` = c.`id`
      LEFT JOIN `department` d ON b.`department_id` = d.`id`
      LEFT JOIN `store` e ON b.`store_id` = e.`id`
      LEFT JOIN `company` f ON e.`company_id` = f.`id`AND d.`company_id` = f.`id`
      WHERE DATE(a.`status_datetime`)>='{$minDate}' AND DATE(a.`status_datetime`)<='{$maxDate}'
      AND a.`status`=0  {$where}
      ORDER BY a.`status_datetime` ASC";
//従業員別に分けて保存
if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
      $datetime[$row['employee_id']][]= $row;
  }
}

//従業員別に一番早い時間のカラムのタイプが出勤(1)じゃないものを保存
$sql = "SELECT (CASE WHEN a.`type` != 1
                     THEN  a.`employee_id`
                     END) AS employee_id
        FROM  `employee_datetime` AS a, (SELECT  `employee_id`,  `type`, MIN(`status_datetime`) AS min_time
                                          FROM  `employee_datetime`
                                          WHERE DATE(`status_datetime`) >= '{$minDate}'
                                          GROUP BY  `employee_id`
                                          ) AS min_date
        WHERE a.`employee_id` = min_date.`employee_id`
        AND a.`status_datetime` = min_date.`min_time`
        GROUP BY a.`employee_id`
        ORDER BY a.`status_datetime` ASC";

if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $preDataId[] = $row;
  }
}

//上から保存したIDで、一番遅い出勤と期限を基準の間
foreach ($preDataId as $key => $value) {
  $sql = "SELECT a.`id`,a.`employee_id`,a.`type`,a.`status_datetime`
          FROM `employee_datetime` AS a,(SELECT  MAX(`status_datetime`) as max_time
                                         FROM  `employee_datetime`
                                         WHERE `type`=1 AND `status`=0
                                         AND `status_datetime` < '{$minDate}'
                                         AND `employee_id`='{$value["employee_id"]}') as b
          WHERE a.`status_datetime` >= b.max_time
          AND a.`status_datetime` < '{$minDate}'
          AND `employee_id`='{$value["employee_id"]}'
          ORDER BY a.`status_datetime` ASC";
  if ($res= $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $preMonthData[$row['employee_id']][] = $row;
    }
  }
}
  //echo var_dump($ar[22])."<br><Br>";
foreach ($preMonthData as $key => $value) {
  $datetime[$key] = array_merge($preMonthData[$key],$datetime[$key]);
}

//従業員別に一番遅い時間のカラムのタイプが出勤(4)じゃない保存
$sql = "SELECT (CASE WHEN a.`type` !=4
                     THEN  a.`employee_id`
                     END) AS employee_id
        FROM  `employee_datetime` AS a, (SELECT  `employee_id` ,  `type` , MAX(`status_datetime`) AS max_time
                                          FROM  `employee_datetime`
                                          WHERE DATE(`status_datetime`) <= '{$maxDate}'
                                          GROUP BY  `employee_id`
                                        ) AS max_date
        WHERE a.`employee_id` = max_date.`employee_id`
        AND a.`status_datetime` = max_date.`max_time`
        GROUP BY a.`employee_id`
        ORDER BY a.`status_datetime` ASC";

if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $aftDataId[] = $row;
  }
}
//上から保存したIDで、一番早い退勤と期限を基準の間
foreach ($aftDataId as $key => $value) {
  $sql = "SELECT a.`id`,a.`employee_id`,a.`type`,a.`status_datetime`
          FROM `employee_datetime` AS a,(SELECT  MIN(`status_datetime`) as min_time
                                         FROM  `employee_datetime`
                                         WHERE `type`=4 AND `status`=0
                                         AND `status_datetime` > '{$maxDate}'
                                         AND `employee_id`='{$value["employee_id"]}') as b
          WHERE a.`status_datetime` <= b.min_time
          AND a.`status_datetime` > '{$maxDate}'
          AND `employee_id`='{$value["employee_id"]}'
          ORDER BY a.`status_datetime` ASC";
  if ($res= $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $aftMonthData[$row['employee_id']][] = $row;
    }
  }
}

foreach ($aftMonthData as $key => $value) {
  $datetime[$key] = array_merge($datetime[$key],$aftMonthData[$key]);
}



//新規・修正
if (isset($_POST['modify'])) {
  echo 1;
  $modify = $_POST['modify'];
  $allowColumns = array('id'=>'修正番号','employee_id'=>'従業員番号','type'=>'タイプ','status_datetime'=>'時間');
  foreach ($modify as $key => $value) {
    if ($key != 'id' && empty($value)) {
        $errors[]="修正する".$allowColumns[$key]."を設定してください。";
    }
    if (!in_array($key,array_keys($allowColumns))) {
        $errors[]="不明な情報が入力されました。";
    }
  }

  if (!isset($errors)) {
    $sqlFlag = FALSE;
    $columns = array();
    $values = array();
    foreach ($allowColumns as $key => $value) {
      if ($key != 'id') {
        $columns[] = "`{$key}`";
        $values[] = "'".$_link->real_escape_string($modify[$key])."'";
      }
    }

    if (!empty($modify['id'])) { //update
      foreach ($columns as $key => $value) {
        if ($value != "`id`" && $value != '`employee_id`') {
          $set[] = "$value={$values[$key]}";
        }
      }
      $set = implode(",",$set);
      $sql = "UPDATE `employee_datetime` SET $set WHERE `id`='{$modify["id"]}'";
      if ($res = $_link->query($sql)) {
        $sqlFlag = TRUE;
      }
    }else { //insert
      $columns = implode(',',$columns);
      $values = implode(',',$values);
      $sql = "INSERT INTO `employee_datetime`($columns)VALUES($values)";
      if ($res = $_link->query($sql)) {
        if ($_link->affected_rows == 1) {
          $sqlFlag = TRUE;
        }
      }
    }

  }
}

//削除
if (isset($_POST['delete'])) {
  $id = $_POST['delete'];
  $sql = "UPDATE `employee_datetime` SET `status`=1 WHERE `id`='{$id}'";
  if ($res = $_link->query($sql)) {
    $sqlFlag=TRUE;
  }
}

if (isset($sqlFlag)) {
  if ($sqlFlag) {
    $_link->commit();
    unset($_POST['modify']);
  }else {
    $_link->rollback();
    echo "error";
  }
}
require_once('./incompleteSchedule.tpl.php');
?>
