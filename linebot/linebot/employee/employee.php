<?php
require_once('../require/mysql.php');

//ajax로 선택한 값보이기
if (isset($_POST['select'])) {
  $selectId = $_POST['select'];
  $sql ="SELECT a.`name`,a.`manager`,d.`company_name`,b.`department_name`,c.`store_name`,e.`employment_type`,a.`join`
         FROM `employee`   as a
         LEFT OUTER JOIN `department` as b ON b.`id` = a.`department_id`
         LEFT OUTER JOIN `store`      as c ON c.`id` = a.`store_id`
         LEFT OUTER JOIN `company`    as d ON d.`id` = b.`company_id` OR d.`id` = c.`company_id`
         LEFT OUTER JOIN `employment` as e ON e.`id` = a.`employment_id`
         WHERE a.`id`='{$selectId}'";
   if ($res = $_link->query($sql)) {
     if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
       $select = $row;
     }
     echo json_encode($select);
   }
}

//従業員削除
if (isset($_POST['delemployee'])) {
   $id = $_POST['delemployee'];
   $sql = "UPDATE `employee` SET `status`=1 WHERE `id` = '{$id}'";
   if ($res = $_link->query($sql)) {
     echo "削除されました。";
   }else {
     echo "処理中エラーが発生しました。";
   }
}

if (isset($_POST['delSchedule'])) {
  $id = $_POST['delSchedule'];
  $sql = "UPDATE `line_schedule` SET `status`=1 WHERE `id`= '{$id}'";
  if ($res = $_link->query($sql)) {
    if ($_link->affected_rows == 1) {
      echo "スケジュールを削除しました。";
    }
  }
}
?>
