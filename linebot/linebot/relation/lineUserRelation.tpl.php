<?require_once('../require/header.php');?>
<form method="post" action="./lineUserRelation.php">
  <input type="hidden" name="user[line_id]" value="<?=@$user['line_id']?>">
  <input type="hidden" name="user[id]" value="<?=@$user['id']?>">
  <input type="hidden" name="user[display_name]" value="<?=@$user['display_name']?>">
  <input type="hidden" name="user[name]" value="<?=@$user['name']?>">
  <input type="hidden" name="user[company_name]" value="<?=@$user['company_name']?>">
  <input type="hidden" name="user[department_name]" value="<?=@$user['department_name']?>">
  <input type="hidden" name="user[store_name]" value="<?=@$user['store_name']?>">
  <input type="hidden" name="user[employment_type]" value="<?=@$user['employment_type']?>">
  <input type="hidden" name="user[join]" value="<?=@$user['join']?>">
  <input type="hidden" name="user[manager]" value="<?=@$user['manager']?>">

  <div>
    <p><?="Lineユーザ名：".@$user['display_name']?></p>
    <p>現在設定された従業員：<?=@$user['name']?></p>
    <?=(isset($errorMsgs['line_id']))? $errorMsgs['line_id']:''?>
  </div>

  <span>従業員:</sapn>
  <select id="employee_select" name="user[employee_id]">
    <option value="">--------</option>
    <?for ($i=0; $i < count($employee) ; $i++) {?>
        <option value="<?=$employee[$i]['id']?>"><?=$employee[$i]['name']?></option>
    <?}?>
  </select>
  <?=(isset($errorMsgs['employee_id']))? $errorMsgs['employee_id']:''?>

    <table class="type02">
      <tr>
        <th>お名前</th>
        <td id="select_name"><?=@$user['name']?></td>
      </tr>
      <tr>
        <th>所属</th>
        <td id="select_company"><?=@$user['company_name']?></td>
      </tr>
      <tr>
        <th>部署</th>
        <td id="select_department"><?=@$user['department_name']?></td>
      </tr>
      <tr>
        <th>店舗</th>
        <td id="select_store"><?=@$user['store_name']?></td>
      </tr>
      <tr>
        <th>職種</th>
        <td id="select_employment"><?=@$user['employment_type']?></td>
      </tr>
      <tr>
        <th>入社日</th>
        <td id="select_join"><?=@$user['join']?></td>
      </tr>
      <tr>
        <th>マーナージャ権限</th>
        <td id="select_manager"><?=@$user['manager']?></td>
      </tr>
  </table>
  <input type="submit" value="<?=(!empty($user['relation_id']))? "変更":"設定"?>">
  <input type="button" value="戻る" onclick="location.href='./lineUserSearch.php'">
</form>
