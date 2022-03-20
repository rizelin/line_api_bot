<?require_once('../require/header.php');?>
<style>
    span.hole a{font-family:tahoma; font-size:20px; color:#FF6C21;}
    span.blue a{font-family:tahoma; font-size:20px; color:#0000FF;}
    span.black a{font-family:tahoma; font-size:20px; color:#000000;}
    table.month_schdule {border: 1px solid;}
    table.month_schdule > tbody {display: block; width: 700px;margin:auto;}
    /* table.schdule > tbody >tr{height: 100px;} */
    table.month_schdule > tbody >tr >th{width: 100px;height: 50px;border:solid 1px;vertical-align: middle;}
    table.month_schdule > tbody >tr >td{width: 100px;height: 100px;border:solid 1px;}
</style>
<div class="body">
    <table class="main_table month_schdule">
        <tr>
            <th><a href=<?= 'monthScheduleManagement.php?year='.$preyear.'&month='.$month.'&day=1'; ?>>◀◀</a></th>
            <th><a href=<?= 'monthScheduleManagement.php?year='.$prev_year.'&month='.$prev_month.'&day=1';?>>◀</a></th>
            <th colspan="3">
                <a href=<?= 'monthScheduleManagement.php?year='. $thisyear .'&month='. $thismonth .'&day=1'; ?>>
                <?= $year. '年' .$month. '月' ?>
                </a>
            </th>
            <th><a href=<?= 'monthScheduleManagement.php?year='.$next_year.'&month='.$next_month.'&day=1';?>>▶</a></th>
            <th><a href=<?= 'monthScheduleManagement.php?year='.$nextyear.'&month='.$month.'&day=1'; ?>>▶▶</a></th>
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
        <?$day=1;//표시날짜
        $this_week = 1;
        $_SESSION['month_days'] = array();
        for ($i=1; $i <= $total_week; $i++) { ?>
            <tr>
            <?for($j=0; $j < 7; $j++) {
                echo '<td>';
                if (!(($i == 1 && $j < $start_week) || ($i == $total_week && $j > $last_week))) {
                    if ($j == 0) {
                        $style = "hole";
                    } else if ($j == 6){
                        $style = "blue";
                    } else {
                        $style = "black";
                    }
                    if ($j==0 && $i > 1) {
                        $this_week += 1;
                    }
                    //나중에 바꿀지 생각다시 해보기
                    $_SESSION['month_days'][$this_week][] = $day;

                    if($year == $thisyear && $month == $thismonth && $day == date("j")){
                        //당일
                        echo '<span style="font-weight: bold; font-size:20px;"><a href="weekScheduleManagement.php?year='.$year.'&month='.$month.'&this_week='.$this_week.'&start_week='.$start_week.'&last_week='.$last_week.'">'.$day.'</a></span>';
                    } else {
                        //그외의 날
                        echo '<span class='.$style.'><a href="weekScheduleManagement.php?year='.$year.'&month='.$month.'&this_week='.$this_week.'&start_week='.$start_week.'&last_week='.$last_week.'">'.$day.'</a></span>';
                    }
                    $day += 1;
                }
                echo '</td>';
            }?>
            </tr>
        <?}?>
    </table>
</div>

<?require_once('require/footer.php');?>
