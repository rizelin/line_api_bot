<?php
require_once("../require/mysql.php");
require_once('../require/login.php');

/*종업원 정보에서 이상한 정보는 에러로 보여주기
  1.line연결 안된 것.
  2.고용형태/회사/부서/점포가 삭제된 종업원 정보가 있을 시.
*/
//1.line 연결안된 정보가 있을 시
  $sql = "SELECT a.`id`,a.`display_name` FROM `line_info` as a
          LEFT OUTER JOIN `line_relation` as b
          ON a.`id`= b.`line_id`
          WHERE b.`line_id` IS NULL AND `status`=1";
  if ($res = $_link->query($sql)) {
      while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $line[] = $row;
      }
  }

//2.status가 1인 값을 가지고 있을 때
  $sql = "SELECT a.`id`,a.`name`,b.`status` as department,c.`status` as store,d.`status` as employment,e.`status` as company
          FROM `employee` as a
          LEFT OUTER JOIN `department` as b ON a.`department_id` = b.`id`
          LEFT OUTER JOIN `store` as c      ON a.`store_id` = c.`id`
          LEFT OUTER JOIN `employment` as d ON a.`employment_id` = d.`id`
          LEFT OUTER JOIN `company` as e    ON b.`company_id`=e.`id` OR c.`company_id`=e.`id`
          WHERE a.`status`=0 AND (b.`status`=1 OR c.`status`=1 OR d.`status`=1 OR e.`status`=1)
          GROUP BY a.`id`;
          ";
  if ($res = $_link->query($sql)) {
    while ($row =  $res->fetch_array(MYSQLI_ASSOC)) {
      if ($row['department'] == 1) {$row['error_info'][] = "部署"; }
      if ($row['store']) { $row['error_info'][] = "店舗"; }
      if ($row['company']) { $row['error_info'][] = "会社"; }
      if ($row['employment']) { $row['error_info'][] = "雇用区分";}
      $row['error_info'] = implode(",",$row['error_info']);
      $affiliation[] = $row;
    }
  }

require_once('./incompleteEmployee.tpl.php');
?>
