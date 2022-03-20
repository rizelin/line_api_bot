<?php
require_once('../require/mysql.php');
require_once('../require/login.php');

$thisyear = date('Y');//4자리 연도
$thismonth = date('n');//0을 포함않는 월
$today = date('j');//0을 포함않는 일
//$year,$month 연,월 값이 없으면 현재 날짜
$year = isset($_GET['year']) ? $_GET['year'] : $thisyear;
$month = isset($_GET['month']) ? $_GET['month'] :$thismonth;
$day = isset($_GET['day']) ? $_GET['day'] : $today;
//전월,다음월
$prev_month = $month - 1;
$next_month = $month + 1;
//이해가 안됨 값을 동일시 한다?
$prev_year = $next_year = $year;
if($month == 1){
    $prev_month = 12;
    $prev_year = $year - 1;
}else if($month == 12){
    $next_month = 1;
    $next_year = $year + 1;
}
//내려 올 때 마다 정의값이 들어간다
$preyear = $year - 1;
$nextyear = $year + 1;
//이유를 모르겠음
// $predate = date("Y-m-d", mktime(0,0,0,$month - 1, 1, $year));
// $nextdate = date("Y-m-d", mktime(0,0,0,$month + 1, 1m $year));
//1.총일수 구하기
$max_day = date('t',mktime(0,0,0,$month,1,$year));
//2.시작요일 구하기
$start_week = date("w",mktime(0,0,0,$month,1,$year));
//3.총 몇 주인지 구하기
$total_week = ceil(($max_day + $start_week) / 7);
//4.마지막 요일 구하기
$last_week = date('w',mktime(0,0,0,$month,$max_day,$year));

require_once("./monthScheduleManagement.tpl.php");
?>
