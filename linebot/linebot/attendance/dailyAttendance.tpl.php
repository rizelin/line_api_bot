<?require_once('../require/header.php');?>
<div class="body">
    <div class="title_name">
        <h1>集計</h1>
    </div>
    <form method="post" action="./dailyAttendance.php">
    <span>所属</span>
      <select name="search[company_id]">
          <option value="">全ての所属</option>
          <?for ($i=0; $i < count($company); $i++) {?>
              <option value="<?=$company[$i]['id']?>" <?=($employee['company_id']==$company[$i]['id'])?'selected':''?>><?=$company[$i]['company_name']?></option>
          <?}?>
      </select>
      <span>雇用区分</span>
        <select name="search[employment_id]">
              <option value="">全ての雇用区分</option>
            <?for ($i=0; $i < count($employment); $i++) {?>
                <option value="<?=$employment[$i]['id']?>" <?=($employee['employment_id'] == $employment[$i]['id'])? 'selected':''?>><?=$employment[$i]['employment_type']?></option>
            <?}?>
        <select>
      <input type="text" class="testDatepicker" name="search[date]" value="<?=$date?>">
      <input type="submit" value="検索">
    </form>

    <div>
      <span></span>
      <input type="button" value="<<" name="search[before]">
      <input type="button" value="今日" name="search[today]">
      <input type="button" value=">>" name="search[after]">
    </div>
    <div class="main_table">
        <table class="type11">
          <thead>
            <tr>
              <th scope="cols">NO</th>
              <th scope="cols">名前</th>
              <th scope="cols">出勤・退勤</th>
              <th scope="cols">休憩</th>
              <th scope="cols">労働合計</th>
              <th scope="cols">休憩合計</th>
              <th scope="cols">所定</th>
              <th scope="cols">打刻編集</th>
              <th scope="cols">遅刻</th>
              <th scope="cols">早退</th>
            </tr>
          </thead>
          <tbody>
            <?foreach ($employees as $key => $array) {?>
              <tr>
                <td><?=$key+1?></td>
                <td><?=$array['name']?></td>
                <td>
                  <?for ($i=0; $i < count($normalAttendance[$array['id']]); $i++) {
                      if ($normalAttendance[$array['id']][$i]['type'] == 1) {?>
                          <p><span>出</span> <?=substr($normalAttendance[$array['id']][$i]['status_datetime'],5,11)?></p>
                          <?=($normalAttendance[$array['id']][$i+1]['type']==4)?'<p><spsn>退</span> '.substr($normalAttendance[$array['id']][$i+1]['status_datetime'],5,11).'</p>':''?>
                      <?}?>
                  <?}?>
                </td>
                <td>
                  <?for ($i=0; $i < count($normalRest[$array['id']]); $i++) {
                      if ($normalRest[$array['id']][$i]['type'] == 2) {?>
                          <p><spsn>始</span> <?=substr($normalRest[$array['id']][$i]['status_datetime'],5,11)?></p>
                          <?=($normalRest[$array['id']][$i+1]['type']==3)?'<p><spsn>終</span> '.substr($normalRest[$array['id']][$i+1]['status_datetime'],5,11).'</p>':''?>
                      <?}?>
                  <?}?>
                </td>
                <td><?=$totalTimes['work'][$array['id']]?></td>
                <td><?=$totalTimes['rest'][$array['id']]?></td>
                <td><?=(isset($totalTimes['work'][$array['id']])&&isset($totalTimes['rest'][$array['id']]))?$totalTimes['work'][$array['id']]-$totalTimes['rest'][$array['id']]:''?></td>
                <td><?=(in_array($array['id'],$errors))?"<a href='../incomplete/incompleteSchedule.php?date=".$date."#tr".$array['id']."'>編集</a>":''?></td>
                <td></td>
                <td></td>
              </tr>
            <?}?>
          </tbody>
        </table>
    </div>
</div>

<script>

$(function(){
    $('.testDatepicker').datepicker({
      showOn: 'both',
      prevText: '前月',
      nextText: '来月',
      monthNames: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
      dayNamesMin: ['月','火','水','木','金','土','日'],
      dateFormat: "yy/mm/dd"
    });
});

</script>
<?require_once("require/footer.php");?>
