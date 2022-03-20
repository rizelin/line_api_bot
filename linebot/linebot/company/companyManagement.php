<?php
//ini_set("display_errors", 1);
require_once('../require/mysql.php');
require_once('../require/login.php');


if (isset($_GET['id'])) {
  $company['id'] = $_GET['id'];
  $sql = "SELECT a.`id`,GROUP_CONCAT(DISTINCT b.`id`) as department_id,GROUP_CONCAT(DISTINCT c.`id`) as store_id,a.`company_name`,GROUP_CONCAT(DISTINCT b.`department_name`) as department, GROUP_CONCAT(DISTINCT c.`store_name`) as store
          FROM `company` as a
          LEFT OUTER JOIN `department` as b ON a.`id` = b.`company_id` AND b.`status` = 0
          LEFT OUTER JOIN `store`      as c ON a.`id` = c.`company_id` AND c.`status` = 0
          WHERE a.`id` = '{$company["id"]}'";

  if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $company = $row;
    }
  }

//부사,매장의 원래 값들을 배열로 저장
  $company['department'] = explode(',',$company['department']);
  $company['department']['update'] =$company['department'];
  $dCnt = count($company['department']['update']);

  $company['store'] = explode(',',$company['store']);
  $company['store']['update'] =$company['store'];
  $sCnt = count($company['store']['update']);
}


if ($_POST['company']) {
  $company = $_POST['company'];
  $allowColumns = array('id' => '会社ID',
                        'department_id' => '部署ID',
                        'store_id' => '店舗ID',
                        'company_name' => '会社名',
                        'department'   => '部署情報',
                        'store'        => '店舗情報');
  $error = array(1 => 'が未入力です', 2 => 'が不正な入力です');


  if (empty($company['company_name'])) {
    $errors['company_name'] = 1;
  }

  foreach ($company as $key => $value) {
    if (!in_array($key,array_keys($allowColumns))) {
      $errors[$key] = 2;
    }
  }


  //会社DBに保存した後、ガイシャIDと部署・店舗保存
  if(isset($errors)){
    foreach ($errors as $key => $value) {
      $errorMsgs[$key] = "$allowColumns[$key]$error[$value]";
    }
  }else {
    //department 수정 신규를 한 배열로 만들기
    if (isset($company['department']['update']) && isset($company['department']['insert'])) {
        $departments = array_unique(array_merge($company['department']['update'],$company['department']['insert']));
    }elseif (isset($company['department']['update']) && !isset($company['department']['insert'])) {
        $departments = array_unique($company['department']['update']);
    }elseif (!isset($company['department']['update']) && isset($company['department']['insert'])) {
        $departments = array_unique($company['department']['insert']);
    }
    $departments = array_values(array_filter(array_map('trim',$departments)));
    //store
    if (isset($company['store']['update']) && isset($company['store']['insert'])) {
      $stores = array_unique(array_merge($company['store']['update'],$company['store']['insert']));
    }elseif (isset($company['store']['update']) && !isset($company['store']['insert'])) {
      $stores = array_unique($company['store']['update']);
    }elseif (!isset($company['store']['update']) && isset($company['store']['insert'])) {
      $stores = array_unique($company['store']['insert']);
    }
    $stores = array_values(array_filter(array_map('trim',$stores)));


    if (empty($company['id'])) {
      //insert
        $sqlFlg = FALSE;
        $sql = "INSERT INTO `company` (`company_name`) VALUES ('{$company["company_name"]}')";
        if ($res = $_link->query($sql)) {
            if ($_link->affected_rows == 1) {
                $id = $_link->insert_id;

                if (isset($departments)) {
                    foreach ($departments as $key => $value) {
                        $dValues[] = "('{$id}','{$value}')";
                    }
                    $dValues = implode(",",$dValues);
                    $sql = "INSERT INTO `department` (`company_id`,`department_name`)VALUES {$dValues}";
                    if ($res = $_link->query($sql)) {
                        if ($_link->affected_rows == 1) {
                          $sqlFlg = TRUE;
                        }
                    }
                }

                if (isset($stores)) {
                  $sql = FALSE;
                    foreach ($stores as $key => $value) {
                        $sValues[] = "('{$id}','{$value}')";
                    }
                    $sValues = implode(",",$sValues);
                    $sql = "INSERT INTO `store` (`company_id`,`store_name`)VALUES {$sValues}";
                    if ($res = $_link->query($sql)) {
                        if ($_link->affected_rows != 0) {
                          $sqlFlg = TRUE;
                        }
                    }
                }
            }
        }
      }else {
      //update
        $sqlFlg = FALSE;
        if (!empty($company['department_id'])) { $department_id = explode(',',$company['department_id']); }
        if (!empty($company['store_id']))      { $store_id = explode(',',$company['store_id']); }

        $sql="UPDATE `company` SET `company_name` = '{$company["company_name"]}' WHERE `id` = '{$company["id"]}'";
        if ($res = $_link->query($sql)) {
          $sqlFlg = TRUE;
              //department UPDATE
                foreach ($department_id as $key => $value) {
                  $sqlFlg = FALSE;
                  if (isset($departments[$key])) {//순서대로 수정하기
                      $sql = "UPDATE `department` SET `department_name`= '{$departments[$key]}' WHERE `id` ='{$value}'";
                  }else { //id가 입력한 값보다 많을 떄 남은 id는 삭제처리
                      $sql = "UPDATE `department` SET `status`='1' WHERE `id` = '{$value}'";
                  }
                  unset($departments[$key]);
                    if ($res = $_link->query($sql)) { $sqlFlg = TRUE; }
                }
              //department INSERT
                if (!empty($departments)) { //입력할 값이 남아있으면 신규로 넣기
                  foreach ($departments as $key => $value) {
                    $sqlFlg = FALSE;
                      $dValues[] = "('{$company['id']}','{$value}')";
                  }
                  $dValues = implode(",",$dValues);
                  $sql = "INSERT INTO `department` (`company_id`,`department_name`) VALUES {$dValues}";
                  if ($res = $_link->query($sql)) {
                      if ($_link->affected_rows != 0) { $sqlFlg = TRUE; }
                  }
                }

                //store UPDATE
                  foreach ($store_id as $key => $value) {
                    $sqlFlg = FALSE;
                    if (isset($stores[$key])) {
                        $sql = "UPDATE `store` SET `store_name`= '{$stores[$key]}' WHERE `id` ='{$value}'";
                    }else { //id가 입력한 값보다 많을 떄 남은 id는 삭제처리
                        $sql = "UPDATE `store` SET `status`='1' WHERE `id` = '{$value}'";
                    }
                    unset($stores[$key]);
                      if ($res = $_link->query($sql)) {
                          $sqlFlg = TRUE;
                      }
                  }
                //store INSERT
                  if (!empty($stores)) {
                    foreach ($stores as $key => $value) {
                        $sqlFlg = FALSE;
                        $sValues[] = "('{$company['id']}','{$value}')";
                    }
                    $sValues = implode(",",$sValues);
                    $sql = "INSERT INTO `store` (`company_id`,`store_name`) VALUES {$sValues}";
                    if ($res = $_link->query($sql)) {
                        if ($_link->affected_rows != 0) { $sqlFlg = TRUE; }
                    }
                  }
        }
      }

      if ($sqlFlg) {
         $_link->commit();
         header("location:./companySearch.php");
         exit();
      }else {
         $_link->rollback();
         echo "error";
     }
  }
}
require_once('./companyManagement.tpl.php');
?>
