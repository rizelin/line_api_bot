<?php
require_once('../require/mysql.php');
require_once('../require/login.php');

//모든 회사정보와 회사와 연결된 부서,점포의 정보가져오기
$sql = "SELECT a.`id`,a.`company_name`,group_concat(DISTINCT b.`department_name`) as department,group_concat(DISTINCT c.`store_name`) as store
        FROM`company` as a
        LEFT OUTER JOIN `department` as b
        ON b.`company_id` = a.`id` AND b.`status` = 0
        LEFT OUTER JOIN `store` as c
        ON c.`company_id` = a.`id` AND c.`status` = 0
        WHERE a.`status` = 0
        GROUP BY a.`id`
        ";
if ($res = $_link->query($sql)) {
  while($row = $res->fetch_array(MYSQLI_ASSOC)){
    $company[] = $row;
  }
  $cnt = count($company);
}
require_once('./companySearch.tpl.php');
?>
