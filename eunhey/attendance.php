<?php
/*data
일별, 월별, 년별,스케쥴관리,직원검색
*/
require_once("mysql.php");

$sql="SELECT a.`punch_in`,a.`punch_out`,b.`start_time`,b.`end_time`,c.`display_name`,c.`employment_type`,c.`assignment`
      FROM `attendance` as a
      JOIN `rest_time` as b
      ON a.`id` = b.`attendance_id`
      JOIN `line_user` as c
      ON c.`id` = a.`employee_id`
      ";
if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
      $info[] = $row;
  }
}

echo var_dump($info);
?>
<html>
<form>
  <select>
    <option>日別データ</option>
    <option>月別データ</option>
    <option>年別データ</option>
  </select>
  <input type="submit" value="検索">
</form>

</html>
