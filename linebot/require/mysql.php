<?php
session_start();
$_db = array(  //데이터베이스 접속
    'host' => '127.0.0.1',
    'name' => 'vsite_zXZuYxU_db',
    'user' => 'vsite_zXZuYxU',
    'pfwd' => 'xqK4fsz'
);

$_link = mysqli_connect($_db['host'],$_db['user'],$_db['pfwd'],$_db['name']);
$_link->set_charset('utf8');
$_link->autocommit(FALSE);
unset($_db); //unset 삭제태그
 ?>
