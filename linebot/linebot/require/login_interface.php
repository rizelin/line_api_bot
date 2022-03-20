<?
ini_set('display_errors',1);
require_once('../require/mysql.php');

//로그아웃
if(isset($_GET['logout'])){
    unset($_SESSION['id'],$_SESSION['password']);
    header('Location:../require/login_interface.php');
    exit();
}

// unset($_SESSION['id']);

if(isset($_POST['id'])){
    //sha256
    $password = $_POST['password'];
    $hash = hash('sha256',$password);
    //세션
    $_SESSION['id'] = $_POST['id'];
    $_SESSION['password'] = $hash;
}

if(isset($_SESSION['id'])){
    header("Location:../employee/employeeSearch.php");
    exit();
}
include_once('./login_interface.tpl.php');
 ?>
