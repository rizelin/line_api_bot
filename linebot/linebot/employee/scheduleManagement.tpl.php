<?require_once('../require/header.php');?>

<?=(isset($confirm))?"<p>$confirm</p>":''?>
<form method="post" action="./scheduleManagement.php">
  <input type="hidden" id="id" name="schedule[id]" value="<?=@$schedule['id']?>">
  <input type="hidden" name="schedule[employee_id]" value="<?=@$schedule['employee_id']?>">

  <p id="view" data-num="0">入力フォームを閉じる</p>
  <div id="insert_form">
  <table class="type02">
    <tr>
      <th>適用日付</th>
      <td>
        <input type="date" id="start_date" class="testDatepicker" name="schedule[start_date]" value="<?=@$schedule['start_date']?>">
        <?= isset($errorMsgs['start_date'])? "<span>{$errorMsgs['start_date']}</span>": ''?>
        <?= isset($errorMsgs['date_error'])? "<span>{$errorMsgs['date_error']}</span>": ''?>
      </td>
    </tr>
    <tr>
      <th>適用終了日付</th>
      <td>
          <input type="date" id="end_date" class="testDatepicker" name="schedule[end_date]" value="<?=@$schedule['end_date']?>">
          <?= isset($errorMsgs['end_date'])? "<span>{$errorMsgs['end_date']}</span>": ''?>
      </td>
    </tr>
    <tr>
      <th>出勤曜日
        <div>
          <span id="weekday">平日</span>
          <span id="weekend">週末</span>
        </div>
      </th>
      <td>
        <table>
          <thead>
            <th><label for="day1">月</label></th>
            <th><label for="day2">火</label></th>
            <th><label for="day3">水</label></th>
            <th><label for="day4">木</label></th>
            <th><label for="day5">金</label></th>
            <th><label for="day6">土</label></th>
            <th><label for="day7">日</label></th>
          </thead>
          <tbody>
            <td><input type="checkbox" name="schedule[days][1]" value="1" id="day1" class="weekdays" <?=(isset($schedule['days'][1]))?'checked':''?>></td>
            <td><input type="checkbox" name="schedule[days][2]" value="1" id="day2" class="weekdays" <?=(isset($schedule['days'][2]))?'checked':''?>></td>
            <td><input type="checkbox" name="schedule[days][3]" value="1" id="day3" class="weekdays" <?=(isset($schedule['days'][3]))?'checked':''?>></td>
            <td><input type="checkbox" name="schedule[days][4]" value="1" id="day4" class="weekdays" <?=(isset($schedule['days'][4]))?'checked':''?>></td>
            <td><input type="checkbox" name="schedule[days][5]" value="1" id="day5" class="weekdays" <?=(isset($schedule['days'][5]))?'checked':''?>></td>
            <td><input type="checkbox" name="schedule[days][6]" value="1" id="day6" class="weekend" <?=(isset($schedule['days'][6]))?'checked':''?>></td>
            <td><input type="checkbox" name="schedule[days][7]" value="1" id="day7" class="weekend" <?=(isset($schedule['days'][7]))?'checked':''?>></td>
          </tbody>
          <?= isset($errorMsgs['days'])? "<span>{$errorMsgs['days']}</span>": ''?>
        </table>
      </td>
    </tr>
    <tr>
      <th>出勤時間</th>
      <td>
          <input type="time" id="start_time" name="schedule[start_time]" value="<?=@$schedule['start_time']?>">
          <?= isset($errorMsgs['start_time'])? "<span>{$errorMsgs['start_time']}</span>": ''?>
          <?= isset($errorMsgs['time_error'])? "<span>{$errorMsgs['time_error']}</span>": ''?>
      </td>
    </tr>
    <tr>
      <th>退勤時間</th>
      <td>
          <input type="time" id="end_time" name="schedule[end_time]" value="<?=@$schedule['end_time']?>">
          <?= isset($errorMsgs['end_time'])? "<span>{$errorMsgs['end_time']}</span>": ''?>
      </td>
    </tr>
    <tr>
      <th>休始時間</th>
      <td>
          <input type="time" id="rest_start_time" name="schedule[rest_start_time]" value="<?=@$schedule['rest_start_time']?>">
          <?= isset($errorMsgs['rest_start_time'])? "<span>{$errorMsgs['rest_start_time']}</span>": ''?>
          <?= isset($errorMsgs['rest_error'])? "<span>{$errorMsgs['rest_error']}</span>": ''?>
      </td>
    </tr>
    <tr>
      <th>休終時間</th>
      <td>
          <input type="time" id="rest_end_time" name="schedule[rest_end_time]" value="<?=@$schedule['rest_end_time']?>">
          <?= isset($errorMsgs['rest_end_time'])? "<span>{$errorMsgs['rest_end_time']}</span>": ''?>
      </td>
    </tr>
  </table>
  <input type="submit" value="登録">
  <input type="button" value="すべて消す" id="clear">
  <input type="button" value="戻る" onclick="location.href='./employeeSearch.php'">
</div>
</form>

<table class="type08">
    <thead>
    <tr>
        <th scope="cols">適用日付</th>
        <th scope="row">適用終了日付</th>
        <th scope="row">出勤曜日</th>
        <th scope="row">出勤時間</th>
        <th scope="row">退勤時間</th>
        <th scope="row">休始時間</th>
        <th scope="row">休終時間</th>
        <th scope="row">所定</th>
        <th scope="row"></th>
    </tr>
    </thead>
    <tbody>
      <?for ($i=0; $i < count($schedules); $i++) {?>
        <tr id="schedule<?=$i?>" class="sch">
            <td id="start_date<?=$schedules[$i]['id']?>"><?=@$schedules[$i]['start_date']?></td>
            <td id="end_date<?=$schedules[$i]['id']?>"><?=@$schedules[$i]['end_date']?></td>
            <td id="week<?=$schedules[$i]['id']?>" data-week="<?=$schedules[$i]['week']?>"><?=@$week[$i]?></td>
            <td id="start_time<?=$schedules[$i]['id']?>"><?=@$schedules[$i]['start_time']?></td>
            <td id="end_time<?=$schedules[$i]['id']?>"><?=@$schedules[$i]['end_time']?></td>
            <td id="rest_start_time<?=$schedules[$i]['id']?>"><?=@$schedules[$i]['rest_start_time']?></td>
            <td id="rest_end_time<?=$schedules[$i]['id']?>"><?=@$schedules[$i]['rest_end_time']?></td>
            <td id="office_hours<?=$schedules[$i]['id']?>"><?=@$schedules[$i]['office_hours']?></td>
            <td>
              <input type="button" class="modify" value="修正" data-id="<?=@$schedules[$i]['id']?>">
                <input type="hidden" name="employee_id" value="<?=@$schedule['employee_id']?>">
                <input type="button" value="削除" onclick="delecteSchedule(<?=@$schedules[$i]['id']?>,<?=$i?>)">
            </td>
        </tr>
    <?}?>
    </tbody>
</table>

<script>
  //modify
  $('.modify').click(function(){
      var id = $(this).data('id');
      $('#id').val(id);
      $('#start_date').val($('#start_date'+id).text());
      $('#end_date').val($('#end_date'+id).text());
      $('#start_time').val($('#start_time'+id).text());
      $('#end_time').val($('#end_time'+id).text());
      $('#rest_start_time').val($('#rest_start_time'+id).text());
      $('#rest_end_time').val($('#rest_end_time'+id).text());

      var week = $('#week'+id).data('week').toString();
      var days = week.split('');
      for (var i = 0; i < days.length; i++) {
          if (days[i] == 1) { $('#day'+(i+1)).prop('checked',true); }
          else { $('#day'+(i+1)).prop('checked',false); }
      }
  });

  $('#weekday').click(function(){
    if ($('.weekdays').prop('checked')) {
      $('.weekdays').prop('checked',false);
    }else {
      $('.weekdays').prop('checked',true);
    }
  });

  $('#weekend').click(function(){
    if ($('.weekend').prop('checked')) {
      $('.weekend').prop('checked',false);
    }else {
      $('.weekend').prop('checked',true);
    }
  });

  $('#clear').click(function(){
    $('#id').val('');
    $('#start_date').val('');
    $('#end_date').val('');
    $('#start_time').val('');
    $('#end_time').val('');
    $('#rest_start_time').val('');
    $('#rest_end_time').val('');
    for (var i = 0; i <= 6; i++) {
      $('#day'+(i+1)).prop('checked',false);
    }
  });

  $('#view').click(function(){
    var num = $(this).data('num');
    if (num == 0) {
      $(this).data('num',1);
      $(this).text('入力フォームを開く');
      $('#insert_form').fadeOut();
      $('#insert_form').css('visibility','hidden');
    }else {
      $(this).data('num',0);
      $(this).text('入力フォームを閉じる');
      $('#insert_form').fadeIn();
      $('#insert_form').css('visibility','visible');
    }
  });

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

<?require_once('require/footer.php');?>
