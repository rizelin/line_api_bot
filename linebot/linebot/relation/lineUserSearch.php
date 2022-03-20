<?php
require_once('../require/mysql.php');
require_once('../require/login.php');

/*라인 정보를 모두 가져와서 보여준다.
가져온 값이 linerelation디비에 값이 있고 없음으로 표시를 다르게
*/
$sql = "SELECT a.`id`,a.`display_name`,group_concat(c.`name` separator '/') as name FROM `line_info` as a
        LEFT OUTER JOIN `line_relation`as b ON a.`id`=b.`line_id`
        LEFT OUTER JOIN `employee`as c ON c.`id`=b.`employee_id`
        GROUP BY a.`id`";
if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
      $user[] = $row;
  }
}

require_once('./lineUserSearch.tpl.php');
?>
