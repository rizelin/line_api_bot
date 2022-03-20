<?require_once('../require/header.php');?>
<div class="body">
    <div class="title_name">
        <h1>エラー一覧</h1>
    </div>
    <form class="body_from" method="post" action="./incompleteSchedule.php">
      <label for="incomplate_company">所属</label>
      <select id="incomplate_company" name="search[company_id]">
          <option>全ての所属</option>
          <?for ($i=0; $i < count($company); $i++) {?>
              <option value="<?=$company[$i]['id']?>" <?=($employee['company_id']==$company[$i]['id'])?'selected':''?>><?=$company[$i]['company_name']?></option>
          <?}?>
      </select><br>
      <label for="incomplate_employee">雇用区分</label>
        <select id="incomplate_employee" name="search[employment_id]">
              <option>全ての雇用区分</option>
            <?for ($i=0; $i < count($employment); $i++) {?>
                <option value="<?=$employment[$i]['id']?>" <?=($employee['employment_id'] == $employment[$i]['id'])? 'selected':''?>><?=$employment[$i]['employment_type']?></option>
            <?}?>
        <select>
      <input type="text" class="monthPicker" name="search[date]" value="<?=substr($date,0,7)?>">
      <input type="submit" value="検索">
    </form>
    <p id="employee_date">期間：<?=$minDate?>~<?=$maxDate?></p>

    <div class="main_table">
      <table class="type09">
          <thead>
            <tr>
                <th scope="cols">従業員名</th>
                <th scope="cols">タイプ</th>
                <th scope="cols">時間</th>
                <th scope="cols">編集</th>
            </tr>
          </thead>
          <tbody>
            <?foreach($datetime as $employeeId => $array){?>
              <tr id="tr<?=$employeeId?>">
              <?for ($i=0; $i < count($array); $i++) {?>
                  <th class="name"><?=$employeeId?><?=$employee[$employeeId]['name']?></th>
                  <?switch ($array[$i]['type']) {
                    case 1:?> <td id="type<?=$array[$i]['id']?>" class="<?=($array[$i-1]['type']==1)?"error":""?>" data-type="1">出勤</td>
                      <?break;?>
                  <?case 2:?><td id="type<?=$array[$i]['id']?>" class="<?=($array[$i-1]['type']==4||$array[$i+1]['type']!=3)?"error":""?>" data-type="2">休憩入り</td>
                      <?break;?>
                  <?case 3:?><td id="type<?=$array[$i]['id']?>" class="<?=($array[$i-1]['type']!=2||$array[$i+1]['type']!=4)?"error":""?>" data-type="3">休憩終わり</td>
                      <?break;?>
                  <?case 4:?><td id="type<?=$array[$i]['id']?>" class="<?=(!isset($array[$i-1]['type'])||$array[$i-1]['type']==4)?"error":""?>" data-type="4">退勤</td>
                      <?break;?>
                  <?}?>
                    <td id="datetime<?=$array[$i]['id']?>"><?=$array[$i]['status_datetime']?></td>
                    <td>
                      <div id="modify_form<?=$array[$i]['id']?>" style="display:none">
                        <?if (isset($errors)) {foreach ($errors as $key => $value) {?> <p><?=$value?></p><?} }?>
                        <form method="post" action="./incompleteSchedule.php#tr<?=$employeeId?>">
                            <table class="type02">
                              <tr>
                                <th>打刻種別</th>
                                <td>
                                  <select id="modify_select<?=$array[$i]['id']?>" name="modify[type]">
                                    <option value="0">----------</option>
                                    <option value="1" <?=($array[$i]['type']==1)?'selected':''?>>出勤</option>
                                    <option value="4" <?=($array[$i]['type']==4)?'selected':''?>>退勤</option>
                                    <option value="2" <?=($array[$i]['type']==2)?'selected':''?>>休憩開始</option>
                                    <option value="3" <?=($array[$i]['type']==3)?'selected':''?>>休憩終了</option>
                                  </select>
                                </td>
                              </tr>
                              <tr>
                                <th>適用日付</th>
                                <td>
                                  <input type="datetime" id="modify_datetime<?=$array[$i]['id']?>" class="datetimePicker" name="modify[status_datetime]" value="<?=$array[$i]['status_datetime']?>">
                                </td>
                              </tr>
                            </table>
                            <input type="hidden" name="modify[employee_id]" value="<?=$employeeId?>">
                            <input type="hidden" id="modify_id<?=$array[$i]['id']?>" name="modify[id]" value="<?=$array[$i]['id']?>">
                            <input type="checkbox" id="add<?=$array[$i]['id']?>" class="incomplete_add" value="<?=$array[$i]['id']?>"><label for="add<?=$array[$i]['id']?>">新規追加</label>
                            <input type="submit" value="登録">
                          </form>
                        </div>

                      <input type="button" class="incomplete_modify" data-id="<?=$array[$i]['id']?>" value="修正">
                      <form method="post" action="./incompleteSchedule.php#tr<?=$employeeId?>" onsubmit="return delete_check()">
                        <input type="hidden" id="modify_id" name="delete" value="<?=$array[$i]['id']?>">
                        <input type="submit" class="incomplete_delete" value="削除">
                      </form>
                    </td>
              </tr>
              <?}//inner?>
            <?}//outer?>
          </tbody>
      </table>
    </div>
</div>
<script>
function delete_check() {
  if (confirm('消しますか？')) {
    return true;
  }else {
    return false;
  }
}
</script>
<? require_once("require/footer.php");?>
