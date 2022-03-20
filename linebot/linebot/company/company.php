<?php
require_once('../require/mysql.php');

if (isset($_POST['delcompany'])) {
  $id = $_POST['delcompany'];
  $sql = "UPDATE `company` SET `status`=1 WHERE `id`= '{$id}'";
  if ($res = $_link->query($sql)) {
    echo "削除されました。";
  }else {
    echo "処理中エラーが発生しました。";
  }
}

?>
