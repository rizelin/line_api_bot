<?php
require_once("../require/mysql.php");
require_once('../require/login.php');

/*유저 정보관리*/
//1.会社
$sql = "SELECT * FROM `company` WHERE `status` = 0";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $company[] = $row;
    }
}
//2-1.部署
$sql ="SELECT a.`id`,b.`id` as company_id,a.`department_name` FROM `department` as a
       JOIN `company` as b ON a.`company_id` = b.`id` WHERE a.`status` = 0";
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

//3.雇用区分
$sql ="SELECT * FROM `employment`";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $employment[] = $row;
    }
}
//修正する従業員の情報
if (isset($_GET['id'])) {
    $employee['employee_id'] = $_GET['id'];
    $sql ="SELECT a.`id` as employee_id,a.`name`,a.`manager`,a.`department_id`,a.`store_id`,a.`employment_id`,a.`join`,a.`resign`,d.`id` as company_id
           FROM `employee`   as a
           LEFT OUTER JOIN `department` as b ON b.`id` = a.`department_id`
           LEFT OUTER JOIN `store`      as c ON c.`id` = a.`store_id`
           JOIN `company`    as d ON d.`id` = b.`company_id` OR d.`id` = c.`company_id`
           WHERE a.`id`='{$employee["employee_id"]}'";
    if ($res = $_link->query($sql)) {
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $employee = $row;
        }
    }
}

if (isset($_POST['employee'])) {
    $employee = $_POST['employee'];
    $allowColumns = array( 'employee_id'   =>  'ユーザーID'
                          ,'company_id'    =>  '会社ID'
                          ,'manager'       =>  'マネージャー権限'
                          ,'name'          =>  'お名前'
                          ,'department_id' =>  '部署'
                          ,'store_id'      =>  '店舗'
                          ,'employment_id' =>  '雇用形態'
                          ,'join'          =>  '入社日'
                          ,'resign'        =>  '退職日'
                         );
    $requireInputs = array('name','employment_id','join');
    $notSave = array('employee_id','company_id','manager');
    $error = array(1 => 'が未入力です',
                   2 => 'が不正な入力です',
                   3 => '部署たまは店舗を1つ以上設定してください。');

    foreach ($requireInputs as $key => $value) {
      if (empty($employee[$value])) {
          $errors[$value] = 1;
      }
    }

    foreach ($employee as $key => $value) {
      if (!in_array($key,array_keys($allowColumns))) {
          $errors[$key] = 2;
      }
    }

    if (empty($employee['department_id']) && empty($employee['store_id'])) {
        $errors['affiliation'] = 3;
    }


    if (isset($errors)) {
        foreach ($errors as $key => $value) {
            $errorMsgs[$key] = "{$allowColumns[$key]}{$error[$value]}";
        }
    }else {

      $sqlFlg = FALSE;
      $columns = array();
      $values = array();
      foreach ($allowColumns as $key => $val) {
        if(!empty($employee[$key]) && !in_array($key,$notSave)){
          $columns[] = "`{$key}`";
          $values[] = "'".$_link->real_escape_string($employee[$key])."'";
        }

      }
      if (!isset($employee['manager'])) {
        $columns[] = "`manager`";
        $values[] = "'".$_link->real_escape_string(0)."'";
      }else {
        $columns[] = "`manager`";
        $values[] = "'".$_link->real_escape_string($employee['company_id'])."'";
      }

      if (empty($employee['employee_id'])) {
          //insert
          $columns = implode(',',$columns);
          $values = implode(',',$values);
          $sql = "INSERT INTO `employee`($columns) VALUES($values)";
echo $sql;
          if ($res = $_link->query($sql)) {
            if ($_link->affected_rows == 1) {
              $sqlFlg = TRUE;
            }
          }
      }else {
          //update
          foreach ($columns as $key => $value) {
              if ($value != "`employee_id`") {
                  $set[] = "$value={$values[$key]}";
              }
          }
          $set = implode(",",$set);
          $sql = "UPDATE `employee` SET $set WHERE `id`= {$employee['employee_id']}";

          if ($res = $_link->query($sql)) {
            $sqlFlg = TRUE;
          }
      }

      if ($sqlFlg) {
        $_link->commit();
        header("location:./employeeSearch.php");
        exit();
      }else {
        $_link->rollback();
        echo "error";
      }

    }
}
require_once("./employeeManagement.tpl.php");
?>
