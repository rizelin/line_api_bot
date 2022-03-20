<?php
require_once('../require/mysql.php');
require_once('../require/login.php');
/*従業員検索*/
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
       ON a.`company_id` = b.`id`";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $department[] = $row;
    }
}
//2-2.店舗
$sql ="SELECT a.`id`,b.`id` as company_id, a.`store_name`
FROM `store` as a
JOIN `company` as b
ON a.`company_id` = b.`id`
WHERE a.`status` = 0";
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

//従業員検索 : 会社名,部署,店舗,雇用区分,名前
if (isset($_POST['employee'])) {
  $search = $_POST['employee'];
  $searchColumns = array('company_id','department_id','store_id','employment_id','name');

  foreach ($searchColumns as $key => $value) {
    if (!empty($search[$value])) {
        if ($value=='company_id') {
          $where[] = " (b.`company_id`='$search[$value]' OR  c.`company_id`='$search[$value]')";
        }else {
          $where[] =  "`$value`='$search[$value]'";
        }
    }
  }

  if (count($where) >= 1) {
    $where = "AND ".implode(" AND",$where);
  }else {
    $msg = "全ての社員を検索しました。";
  }
}

  //検索
      $sql = "SELECT a.`id`,a.`name`,b.`department_name`,c.`store_name`,d.`company_name`,e.`employment_type`
              FROM `employee`as a
              LEFT OUTER JOIN `department` as b
              ON a.`department_id` = b.`id`
              LEFT OUTER JOIN `store` as c
              ON a.`store_id` = c.`id`
              LEFT OUTER JOIN `company` as d
              ON d.`id` = b.`company_id` OR d.`id` = c.`company_id`
              LEFT OUTER JOIN `employment` as e
              ON e.`id` = a.`employment_id`
              WHERE a.`status` = 0 {$where}";
      if ($res = $_link->query($sql)) {
        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $employee[] = $row;
        }
      }

require_once("./employeeSearch.tpl.php");
?>
