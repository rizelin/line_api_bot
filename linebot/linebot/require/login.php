<?php

if(isset($_SESSION['id'])){
    $sql = "SELECT `manager_name`
    FROM `line_employee_manager`
    WHERE `manager_id` = '{$_SESSION['id']}'
    AND `manager_password` = '{$_SESSION['password']}'";
    if($res = $_link->query($sql)){
        if($res->num_rows == 1){
            if($row = $res->fetch_array(MYSQLI_ASSOC)){
                $user = $row;
            }
        }
    }
}
if(!isset($user)){
    header('Location:../require/login_interface.php?logout');
    exit();
}
?>
