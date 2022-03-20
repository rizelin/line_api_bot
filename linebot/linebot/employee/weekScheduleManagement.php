<?php
require_once('../require/mysql.php');
require_once('../require/login.php');

ini_set('display_errers',1);
if(isset($_GET['year'])){
    $_SESSION['year'] = $_GET['year'];
}
if(isset($_SESSION['yeat'])){
    $year = $_SESSION['year'];
}
if(isset($_GET['month'])){
    $_SESSION['month'] = $_GET['month'];
}
if(isset($_SESSION['month'])){
    $month = $_SESSION['month'];
}
if(isset($_GET['this_week'])){
    $_SESSION['this_week'] = $_GET['this_week'];
}
if(isset($_SESSION['this_week'])){
    $this_week = $_SESSION['this_week'];
}
if(isset($_GET['start_week'])){
    $_SESSION['start_week'] = $_GET['start_week'];
}
if(isset($_SESSION['start_week'])){
    $start_week = $_SESSION['start_week'];
}
if(isset($_GET['last_week'])){
    $_SESSION['last_week'] = $_GET['last_week'];
}
if(isset($_SESSION['last_week'])){
    $last_week = $_SESSION['last_week'];
}
if(isset($_SESSION['month_days'])){
    $month_days = array();
    $month_days = $_SESSION['month_days'];
}
//1.会社
$sql = "SELECT `id`,`company_name` FROM `company` WHERE `status`=0";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $company[] = $row;
    }
}
//2-1.部署
$sql ="SELECT a.`id`,b.`id` as company_id,a.`department_name` FROM `department` as a JOIN `company` as b ON a.`company_id` = b.`id`";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $department[] = $row;
    }
}
//2-2.店舗
$sql ="SELECT a.`id`,b.`id` as company_id, a.`store_name` FROM `store` as a JOIN `company` as b ON a.`company_id` = b.`id` WHERE a.`status` = 0";
if ($res = $_link->query($sql)) {
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $store[] = $row;
    }
}
//就業員
$now_date = date('Y-m-d');
$sql="SELECT `id`,`name`,`store_id`,`department_id`,`employment_id` FROM `employee` WHERE `status` = 0 AND (`resign` > '{$now_date}' OR `resign` IS NULL)";
if($res = $_link->query($sql)){
    $employee_list = array();
    while($row = $res->fetch_array(MYSQLI_ASSOC)){
        $employee_list[] = $row;
    }
}
//종업원 리스트에 회사번호 넣기? 나중에 지울지 검토하기
for ($i=0; $i < count($employee_list); $i++) {
    foreach ($department as $key => $val) {
        if ($val['id'] == $employee_list[$i]['department_id']) {
            $employee_list[$i]['company_id'] = $val['company_id'];
        }
    }
}
//검색 값이 들어오면 세션에 넣는다
if(isset($_POST['employee'])){
    $_SESSION['employee'] = $_POST['employee'];
}
if(isset($_POST['employee_schedule'])){
    $start_date = substr($_POST['employee_schedule']['start_date'],-2);
    $end_date = substr($_POST['employee_schedule']['end_date'],-2);
    $day_date = $end_date - $start_date;
    $week = '0000000';
    $week = substr_replace($week,"1",$_POST['employee_schedule']['week'],1);
    for ($i=$_POST['employee_schedule']['week']; $i <= $day_date+$_POST['employee_schedule']['week']; $i++) {
        if($i >= 7 ){
            $week = substr_replace($week,"1",$i-7,1);
        }else{
            $week = substr_replace($week,"1",$i,1);
        }
    }

    $_POST['employee_schedule']['week'] = $week;
    $employee_schedule = array();
    foreach ($_POST['employee_schedule'] as $key => $value) {
        $schedule_columns[$key] = "`".$key."`";
        if($schedule_values[$key] == 'end_date'){
            echo $schedule_values[$key];
            if(empty($_POST['employee_schedule']['end_date'])){
                $schedule_values[$key] = "'NULL'";
                echo "確認";
            }
        }else{
            $schedule_values[$key] = "'".$_link->real_escape_string($value)."'";
        }
    }
    $schedule_columns = implode($schedule_columns,",");
    $schedule_values = implode($schedule_values,",");
    $sql = "INSERT INTO `line_schedule`({$schedule_columns})VALUES({$schedule_values})";
    if($res = $_link->query($sql)){
        //나중에 변경할 것!
        echo $sql;
        echo "成功";
    }else{
        //나중에 변경할 것!
        echo $sql;
        echo "失敗";
    }
}
//전체의 스케줄을 꺼낸다
$sql="SELECT `id`,`employee_id`,`start_date`,`end_date`,`week`,`start_time`,`end_time`
FROM `line_schedule`
WHERE `status` = 0";
if($res = $_link->query($sql)){
    $week_schedule = array();
    while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
        $week_schedule[] = $row;
    }
}

echo $_SESSION['year'].'-'.$_SESSION['month'].'-'.$_SESSION['this_week'].'-'.$_SESSION['start_week'];
?><pre><?php
//첫 주이면 7일이안될 수 있으므로 더미 요일을 넣는다.
if($_SESSION['this_week'] == 1){

    //주에서 가장 빠른 날짜
    echo $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][0],2,0,STR_PAD_LEFT).'<br>';
    $sql_start_date = $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][0],2,0,STR_PAD_LEFT);
    //주에서 가장 뒤 날짜
    echo $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][count($month_days[$_SESSION['this_week']])-1],2,0,STR_PAD_LEFT).'<br>';
    $sql_last_date = $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][count($month_days[$_SESSION['this_week']])-1],2,0,STR_PAD_LEFT);

    //7일 이하의 경우
    if(count($month_days[$_SESSION['this_week']]) < 7){
        echo '七日以下の場合,曜日:'.$_SESSION['start_week'].'<br>';
        var_dump($month_days[$_SESSION['this_week']]);

        $week_days = array();
        for ($i=0; $i < 7; $i++) {
            if($i >= $_SESSION['start_week']){
                $week_days[] = $month_days[$_SESSION['this_week']][$i-$_SESSION['start_week']];
            }else{
                $week_days[] = 'blank';
            }
        }
    }else{
        echo '七日の場合';
    }
var_dump($week_days);
//마지막 주의 경우
}else if($_SESSION['this_week'] == count($month_days)){

    //주에서 가장 빠른 날짜
    echo $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][0],2,0,STR_PAD_LEFT).'<br>';
    $sql_start_date = $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][0],2,0,STR_PAD_LEFT);
    //주에서 가장 뒤 날짜
    echo $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][count($month_days[$_SESSION['this_week']])-1],2,0,STR_PAD_LEFT).'<br>';
    $sql_last_date = $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][count($month_days[$_SESSION['this_week']])-1],2,0,STR_PAD_LEFT);


    //7일 이하의 경우
    if(count($month_days[$_SESSION['this_week']]) < 7){
        echo '七日以下の場合'.$_SESSION['last_week'].'<br>';
        var_dump($month_days[$_SESSION['this_week']]);
        $week_days = array();
        for ($i=0; $i < 7; $i++) {
            if($i <= $_SESSION['last_week']){
                $week_days[] = $month_days[$_SESSION['this_week']][$i];
            }else{
                $week_days[] = 'blank';
            }
        }
    }else{
        echo '七日の場合';
    }
var_dump($week_days);
//첫 주와 마지막 주가 아닌경우
}else{

    //주에서 가장 빠른 날짜
    echo $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][0],2,0,STR_PAD_LEFT).'<br>';
    $sql_start_date = $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][0],2,0,STR_PAD_LEFT);
    //주에서 가장 뒤 날짜
    echo $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][count($month_days[$_SESSION['this_week']])-1],2,0,STR_PAD_LEFT).'<br>';
    $sql_last_date = $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][count($month_days[$_SESSION['this_week']])-1],2,0,STR_PAD_LEFT);

    // $sql_test = array();
    // for ($i=0; $i < count($month_days[$_SESSION['this_week']]; $i++) {
    //     if ($i==0) {
    //         $sql_test[] = $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][0],2,0,STR_PAD_LEFT);
    //     }else if($i == count($month_days[$_SESSION['this_week']]){
    //         $sql_test[] = $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$_SESSION['this_week']][count($month_days[$_SESSION['this_week']])-1],2,0,STR_PAD_LEFT);
    //     }else{
    //         // $sql_test[] = $_SESSION['year'].'-'.str_pad($_SESSION['month'],2,0,STR_PAD_LEFT)
    //     }
    // }

//     echo "平日です";
//     $week_days = array();
//     for ($i=0; $i < 7; $i++) {
//         $week_days[] = $month_days[$_SESSION['this_week']][$i];
//     }
// var_dump($week_days);
}
// echo $_SESSION['this_week'];
//
// // //그중 시작요일 끝요일 확인하기
// // for ($i=0; $i < count($week_days); $i++) {
// //     if ($week_days[$i] == 'blank') {
// //         // code...
// //     }else{
// //
// //     }
// // }
// echo count($week_days);
// //스케줄,특정기간+회사id
// $sql="SELECT s.`id` , s.`employee_id` AS shc_id, s.`start_date` , s.`end_date` , s.`week` , s.`start_time` , s.`end_time` , e.`id` AS empl_id, e.`name` , d.`company_id`
//     FROM  `line_schedule` AS s
//     LEFT JOIN  `employee` AS e ON s.`employee_id` = e.`id`
//     LEFT JOIN  `department` AS d ON d.`id` = e.`department_id`
//     WHERE s.`status` =0
//     AND s.`start_date` >=  '{$sql_start_date}'
//     AND s.`end_date` <=  '{$sql_last_date}'";
// if($res = $_link->query($sql)){
//     $week_schedule_test = array();
//     while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
//         $week_schedule_test[] = $row;
//     }
// }
// var_dump($week_schedule_test);

?></pre>
<?require_once('../require/header.php');?>
<style>
    span.hole a{font-family:tahoma; font-size:20px; color:#FF6C21;}
    span.blue a{font-family:tahoma; font-size:20px; color:#0000FF;}
    span.black a{font-family:tahoma; font-size:20px; color:#000000;}
    table.week_schdule {border: 1px solid;}
    table.week_schdule > tbody {width: 1000px;margin:auto;}
    table.week_schdule > tbody >tr >th{width: 170px;height: 50px;border:solid 1px;vertical-align: middle;}
    table.week_schdule > tbody >tr >td{width: 170px;height: 700px;border:solid 1px;}
</style>
<script type="text/javascript">
// 시작하자마자 display none로 가려두고 선택시에 block화 하여서
function click_form(values){
    var cn3 = new Array();
    for (var i = 0; i < 7; i++) {
        if (i == values) {
            $('.form_select'+values).css('display','block');
        }else{
            $('.form_select'+i).css('display','none');
        }
    }
}
</script>
<div class="body">
    <form class="" action="./weekScheduleManagement.php" method="post">
        <label for="employee_company">所属</label><br>
        <select id="employee_company" class="company_select" name="employee[company_id]">
            <option value="">全ての所属</option>
            <?for ($i=0; $i < count($company); $i++) {?>
                <option value="<?=$company[$i]['id']?>" <?=($_SESSION['employee']['company_id']==$company[$i]['id'])?'selected':''?>>
                    <?=$company[$i]['company_name']?>
                </option>
            <?}?>
        </select><br><br>
        <label for="employee_department">部署</label><br>
        <select id="employee_department" class="affiliation affiliation_select" name="employee[department_id]">
            <option class="affiliation_default" value="">
                ------------
            </option>
            <?for ($i=0; $i < count($department); $i++) {?>
                <option class="affiliation<?=$department[$i]['company_id']?>" value="<?=$department[$i]['id']?>" <?=($_SESSION['employee']['department_id']==$department[$i]['id'])?'selected':''?>>
                    <?=$department[$i]['department_name']?>
                </option>
            <?}?>
        </select><br>
        <span>店舗</span><br>
        <select class="affiliation" name="employee[store_id]">
            <option class="affiliation_default" value="">
                ------------
            </option>
            <?for ($i=0; $i < count($store); $i++) {?>
                <option class="affiliation<?=$store[$i]['company_id']?>" value="<?=$store[$i]['id']?>" <?=($_SESSION['employee']['store_id']==$store[$i]['id'])?'selected':''?>>
                    <?=$store[$i]['store_name']?>
                </option>
            <?}?>
        </select><br>
        <input type="submit" value="確認">
    </form>
    <?if(!empty($_SESSION['employee']['name']) && $_SESSION['employee']['name'] != '----'){
        $employee_schedule = array();
        for ($i=0; $i < count($company); $i++) {
            if ($company[$i]['id'] == $_SESSION['employee']['company_id']) {
                echo $company[$i]['id'].':'.$company[$i]['company_name'].'<br>';
                $employee_schedule['company_id'] = $company[$i]['id'];
            }
        }
        for ($i=0; $i < count($department); $i++) {
            if ($department[$i]['id'] == $_SESSION['employee']['department_id']) {
                echo $department[$i]['id'].':'.$department[$i]['department_name'].'<br>';
                $employee_schedule['department_id'] = $department[$i]['id'];
            }
        }
        if (!empty($_SESSION['employee']['store_id'])) {
            for ($i=0; $i < count($store); $i++) {
                if ($store[$i]['id'] == $_SESSION['employee']['store_id']) {
                    echo $store[$i]['id'].':'.$store[$i]['store_name'].'<br>';
                }
            }
        }
        for ($i=0; $i < count($employee_list); $i++) {
            if ($employee_list[$i]['id'] == $_SESSION['employee']['name']) {
                echo $employee_list[$i]['id'].':'.$employee_list[$i]['name'].'<br>';
                $employee_schedule['employee_id'] = $employee_list[$i]['id'];
            }
        }
    }?>
    <table class="main_table week_schdule">
        <tr>
            <th colspan="2"></th>
            <th colspan="3"><?=$year?>年 <?=$month?>月</th>
            <th colspan="2"></th>
        </tr>
        <tr>
            <th>日</th>
            <th>月</th>
            <th>火</th>
            <th>水</th>
            <th>木</th>
            <th>金</th>
            <th>土</th>
        </tr>
        <tr>
            <!-- 날짜 -->
            <?$j = 0;
            for ($i=0; $i < 7; $i++) {
                if($this_week == 1){
                    if($start_week <= $i){?>
                        <!-- 달력 일 기입 -->
                        <!-- 첫주가 일요일이 아닐때 공백부터 메운다 -->
                        <th>
                            <?=isset($month_days[$this_week][$j])?$month_days[$this_week][$j].'日':''?>
                            <?=isset($month_days[$this_week][$j])?"<input id='week{$i}' type='radio' name='form_select_radio' value='{$i}' onclick='click_form(this.value)'><label for='week{$i}'>スケジュール入力</label>:".$i:''?>
                        </th>
                        <?$j += 1;
                    }else{?>
                        <th></th>
                    <?}
                }else {?>
                    <!-- 마지막 주가 토요일로 끝나지 않으면 공백으로 메운다 -->
                    <th>
                        <?=isset($month_days[$this_week][$i])?$month_days[$this_week][$i].'日':''?>
                        <?=isset($month_days[$this_week][$i])?"<input type='radio' name='form_select_radio' value='{$i}'>FROM:".$i:''?>
                    </th>
                <?}
            }?>
        </tr>
        <tr>
            <?$j = 0;
            for ($i=0; $i < 7; $i++) {
                $week_date = $year.'-'.str_pad($month,2,0,STR_PAD_LEFT).'-'.str_pad($month_days[$this_week][$j],2,0,STR_PAD_LEFT);
                $next_date = strtotime($week_date.'+1 days');
                $tomorrow = date('Y-m-d',$next_date);
                //첫번째주
                if($this_week == 1){
                    if($start_week <= $i){?>
                        <td class="">
                            <?if(!empty($_SESSION['employee']['department_id'])){?>
                                <form class="form_select form_select<?=$i?>" action="./weekScheduleManagement.php" method="post" style="display:none;">
                                    <input type="hidden" name="employee_schedule[employee_id]" value="<?=$employee_schedule['employee_id']?>">
                                    <input type="hidden" name="employee_schedule[start_date]" value="<?=$week_date?>">
                                    <input type="hidden" name="employee_schedule[week]" value="<?=$i?>">
                                    <select class="employee_name" name="employee_schedule[employee_id]">
                                        <option class="employee_name_default">
                                            ----
                                        </option>
                                        <?for ($m=0; $m < count($employee_list); $m++) {?>
                                            <option class="employee_name<?=$employee_list[$m]['department_id'] ?>" value="<?=$employee_list[$m]['id']?>" <?=($_SESSION['employee']['name']==$employee_list[$m]['id'])?'selected':''?>>
                                                <?=$employee_list[$m]['name']?>
                                            </option>
                                        <?}?>
                                    </select>
                                    <input type="time" name="employee_schedule[start_time]">~
                                    <input type="time" name="employee_schedule[end_time]"><br>
                                    当日<input type="radio" name="employee_schedule[end_date]" value="<?=$week_date?>" checked='selected'>
                                    翌日<input type="radio" name="employee_schedule[end_date]" value="<?=$tomorrow?>">
                                    休憩時間<br>
                                    <input type="time" name="employee_schedule[rest_hour]" value="<?=$employee_schedule['rest_hour']?>">
                                    <input type="submit" value="確認"><br>
                                </form>
                            <?}?>
                            現在の曜日は<?=$i?>曜日です<br>
                            <?
                            for ($k=0; $k < count($week_schedule); $k++) {
                                echo 'k:'.$k.'<br>';
                                if ($week_schedule[$k]['start_date'] == $week_date) {
                                    for ($l=0; $l < count($employee_list); $l++) {
                                        if($week_schedule[$k]['employee_id'] == $employee_list[$l]['id']){
                                            echo "<p class='affiliation"."{$employee_list[$l]['company_id']}'>";
                                            echo "スケジュールID：".$week_schedule[$k]['id'].'<br>';
                                            echo "会社ID：".$employee_list[$l]['company_id'].'<br>';
                                            echo $employee_list[$l]['name']."さん<br>";
                                            echo $week_schedule[$k]['start_time'].' ~';

                                            if(!isset($week_schedule[$k]['end_date'])){
                                                echo ' '.date('m月d日',$next_date).' ';
                                            }else if($week_schedule[$k]['end_date'] == $tomorrow){
                                                echo $week_schedule[$k]['end_date'].' ';
                                            }
                                            echo $week_schedule[$k]['end_time'];
                                            // echo "  <br>";
                                            // echo "<form class='' action='./weekScheduleManagement.php' method='post'>";
                                            // echo "<input type='hidden' name='' value=''>";
                                            // echo "<input type='submit' name='' value='削除'>";
                                            // echo "</form>";
                                            // echo "</p>";
                                            continue;
                                        }
                                    }
                                }
                            }
                            ?>
                        </td>
                        <?$j += 1;
                    }else{?>
                        <td></td>
                    <?}
                }else {?>
                    <td>
                        <?=isset($month_days[$this_week][$i])?$month_days[$this_week][$i]:''?><?=isset($month_days[$this_week][$i])?"現在の曜日は".$i."です<input type='time' min='10:00' name='employee[start_time]'>~<input type='time' name='employee[end_time]'>
                        <input type='submit' value='確認'>":''?>
                    </td>
                <?}
            }?>
        </tr>
    </table>
</div>
<?require_once('require/footer.php');?>
