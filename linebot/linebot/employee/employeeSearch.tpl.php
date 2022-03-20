<?require_once('../require/header.php');?>
<div class="body">
    <div class="title_name">
        <h1>社員検索</h1>
    </div>
    <form class="body_from" method="post" action="./employeeSearch.php">
          <label for="employee_company">所属</label>
          <select id="employee_company" class="company_select" name="employee[company_id]">
              <option value="">全ての所属</option>
              <?for ($i=0; $i < count($company); $i++) {?>
                  <option value="<?=$company[$i]['id']?>" <?=($employee['company_id']==$company[$i]['id'])?'selected':''?>><?=$company[$i]['company_name']?></option>
              <?}?>
          </select>
          <label for="employee_department">部署</label>
          <select id="employee_department" class="affiliation" name="employee[department_id]">
                <option class="affiliation_default" value="">------------</option>
            <?for ($i=0; $i < count($department); $i++) {?>
                <option class="affiliation<?=$department[$i]['company_id']?>" value="<?=$department[$i]['id']?>"> <?=$department[$i]['department_name']?> </option>
            <?}?>
          </select>
          <span>店舗</span>
            <select class="affiliation" name="employee[store_id]">
                  <option class="affiliation_default" value="">------------</option>
              <?for ($i=0; $i < count($store); $i++) {?>
                  <option class="affiliation<?=$store[$i]['company_id']?>" value="<?=$store[$i]['id']?>"> <?=$store[$i]['store_name']?> </option>
              <?}?>
            </select>
          <span>雇用区分</span>
          <select name="employee[employment_id]">
                <option value="">全ての雇用区分</option>
              <?for ($i=0; $i < count($employment); $i++) {?>
                  <option value="<?=$employment[$i]['id']?>" <?=($employee['employment_id'] == $employment[$i]['id'])? 'selected':''?>><?=$employment[$i]['employment_type']?></option>
              <?}?>
          <select>
        <span>社員名</span>
        <input type="text" name="employee[name]" value="<?=@$employee['name']?>">
        <input type="submit" value="検索">
    </form>
    <a href="./monthScheduleManagement.php">test</a>

    <input type="button" value="新規登録" onclick="location.href='./employeeManagement.php'">
    <input type="button" value="雇用区分管理" onclick="location.href='./employmentManagement.php'">
    <div class="main_table">
        <table class="type09">
            <thead>
            <tr>
                <th scope="cols">社員名</th>
                <th scope="cols">雇用区分</th>
                <th scope="cols">部署</th>
                <th scope="cols">情報設定</th>
                <th scope="cols">スケジュール管理</th>
                <th scope="cols">削除</th>
            </tr>
            </thead>
            <tbody>
              <?for ($i=0; $i < count($employee); $i++) {?>
              <tr id="employee<?=$i?>">
                  <th scope="row" id="employee_name<?=$i?>"><?=$employee[$i]['name']?></th>
                  <td><?=$employee[$i]['employment_type']?></td>
                  <td><?=$employee[$i]['company_name']." ".$employee[$i]['department_name']." ".$employee[$i]['store_name']?></td>
                  <td><input type="button" value="情報管理" onclick="location.href='./employeeManagement.php?id=<?=$employee[$i]['id']?>'"></td>
                  <td><input type="button" value="スケジュール管理" onclick="location.href='./scheduleManagement.php?id=<?=$employee[$i]['id']?>'"></td>
                  <td><input type="button" value="削除" onclick="delecteEmployee('<?=$employee[$i]['id']?>','<?=$i?>');"> </td>
              </tr>
              <?}?>
            </tbody>
        </table>
    </div>
</div>

<script>
//Employee Search
function delecteEmployee(id,num){
  var employee = $('#employee_name'+num).text();
  if (confirm(employee+'を消しますか？')) {
    $.ajax({
      type: 'POST',
      url:'../employee/employee.php',
      data: {delemployee:id},
      success: function(result){
        $('#employee'+num).remove();
        alert(result);
      },
      error: function(){
        alert("処理中エラーが発生しました。");
      }
    });
  }
}
</script>
<?require_once("require/footer.php");?>
