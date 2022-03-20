<?php
require_once('../require/mysql.php');
require_once('../require/login.php');

/*라인정보와 종업원을 연결시키는 작업*/
//LINE情報と紐づいてる従業員情報
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $sql="SELECT a.`id` as line_id,a.`display_name`,b.`id`,c.`name`,c.`manager`,c.`join`,c.`resign`,d.`department_name`,e.`store_name`,f.`company_name`,g.`employment_type`
        FROM `line_info` as a
        LEFT OUTER JOIN `line_relation`as b ON a.`id`=b.`line_id`
        LEFT OUTER JOIN `employee`as c ON c.`id`=b.`employee_id`
        LEFT OUTER JOIN `department` as d ON d.`id` = c.`department_id`
        LEFT OUTER JOIN `store`      as e ON e.`id` = c.`store_id`
        LEFT OUTER JOIN `company`    as f ON f.`id` = d.`company_id` OR f.`id` = e.`company_id`
        LEFT OUTER JOIN `employment` as g ON g.`id` = c.`employment_id`
        WHERE a.`id`='{$id}'";
  if ($res = $_link->query($sql)) {
    if ($row = $res->fetch_array(MYSQLI_ASSOC)) {
      $user = $row;
    }
    $user['manager'] = (!is_null($row['manager']))? ($row['manager'] == 0)? "なし":"あり" :'';
  }
}
//Select：全ての従業員（退社除く）
$sql = "SELECT `id`,`name`
        FROM `employee`
        WHERE `resign` >= NOW() OR `resign` IS NULL OR `resign`='0000-00-00'";
if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
      $employee[] = $row;
  }
}

if (isset($_POST['user'])) {
  $user = $_POST['user'];
  $allowColumns = array('id' => 'relation ID'
                      ,'line_id' => 'Lineユーザー'
                      ,'employee_id' => '従業員');
  $requireInputs = array('line_id','employee_id');
  $error = array(1 => 'を選択してください', 2=> 'が不正な入力です');

  foreach ($requireInputs as $key => $value) {
    if (empty($user[$value])) {
      $errors[$value] = 1;
    }
  }
  foreach ($allowColumns as $key => $value) {
    if (!in_array($key,array_keys($user))) {
      $errors[$key] = 2;
    }
  }


  if (isset($errors)) {
    foreach ($errors as $key => $value) {
      $errorMsgs[$key] = "$allowColumns[$key]$error[$value]";
    }
  }else {
    $sqlFlg = FALSE;
    if (empty($user['id'])) { //relation ID
      //insert
      $sql = "INSERT INTO `line_relation`(`line_id`,`employee_id`) VALUES('{$user["line_id"]}','{$user["employee_id"]}')";
      if ($res = $_link->query($sql)) {
        if ($_link->affected_rows == 1) {
          $sqlFlg = TRUE;
        }
      }
    }else {
      //update
      $sql = "UPDATE `line_relation` SET `employee_id`='{$user["employee_id"]}' WHERE `id`='{$user["id"]}' AND `line_id`='{$user["line_id"]}'";
      if ($res = $_link->query($sql)) {
          $sqlFlg = TRUE;
      }
    }

    if ($sqlFlg) {
      $_link->commit();
      header('location:./lineUserSearch.php');
      exit();
    }else {
      $_link->rollback();
      echo "error";
    }
  }
}

require_once('./lineUserRelation.tpl.php');
?>
