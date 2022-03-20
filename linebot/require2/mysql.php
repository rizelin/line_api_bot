<?php
session_start();

$_db = array(  //데이터베이스 접속
    'host' => '127.0.0.1',
    'name' => 'vsite_4sBqLAH_db',
    'user' => 'vsite_4sBqLAH',
    'pfwd' => 'd792OZ4'
);

$_link = mysqli_connect($_db['host'],$_db['user'],$_db['pfwd'],$_db['name']);
$_link->set_charset('utf8');
$_link->autocommit(FALSE);
unset($_db); //unset 삭제태그

/*
if(!$_link){
	echo '[연결실패] : '.mysql_error().'';
	die('MySQL 서버에 연결할 수 없습니다.');
} else {
	echo '[연결성공]';
}
*/
 ?>
