<?require_once("../require/header.php");?>
<p><?=$employee['display_name']?></p>
    <form method="post" action="./employeeManagement.php">
      <input type="hidden" name="employee[employee_id]" value="<?=$employee['employee_id']?>">
      <?=(isset($confirm))? "<p>$confirm</p>":''?>
        <table class="type02">
          <tr>
            <th>お名前</th>
            <td>
                <input type="text" name="employee[name]" value="<?=@$employee['name']?>">
                <?= isset($errorMsgs['name'])? "<span>{$errorMsgs['name']}</span>": ''?>
            </td>
          </tr>
          <tr>
            <th>所属</th>
            <td>
              <select class="company_select" name="employee[company_id]">
                  <?for ($i=0; $i < count($company); $i++) {?>
                      <option value="<?=$company[$i]['id']?>" <?=($employee['company_id']==$company[$i]['id'])?'selected':''?>><?=$company[$i]['company_name']?></option>
                  <?}?>
              </select>
              <?= isset($errorMsgs['company_id'])? "<span>{$errorMsgs['company_id']}</span>": ''?>
            </td>
          <tr>
          <tr>
            <th>部署</th>
            <td>
              <select class="affiliation" name="employee[department_id]">
                    <option class="affiliation_default" value="0">------------</option>
                <?for ($i=0; $i < count($department); $i++) {?>
                    <option class="affiliation<?=$department[$i]['company_id']?>" value="<?=$department[$i]['id']?>"<?=($employee['department_id'] ==$department[$i]['id'])? 'selected':''?> style="display:none;"> <?=$department[$i]['department_name']?> </option>
                <?}?>
              </select>
              <?= isset($errorMsgs['department_id'])? "<span>{$errorMsgs['department_id']}</span>": ''?>
              <?= isset($errorMsgs['affiliation'])? "<span>{$errorMsgs['affiliation']}</span>": ''?>
            </td>
          </tr>
          <tr>
          <tr>
            <th>店舗</th>
            <td>
              <select class="affiliation" name="employee[store_id]">
                    <option class="affiliation_default" value="0">------------</option>
                <?for ($i=0; $i < count($store); $i++) {?>
                    <option class="affiliation<?=$store[$i]['company_id']?>" value="<?=$store[$i]['id']?>"<?=($employee['store_id'] ==$store[$i]['id'])? 'selected':''?> style="display:none;"> <?=$store[$i]['store_name']?> </option>
                <?}?>
              </select>
              <?= isset($errorMsgs['store_id'])? "<span>{$errorMsgs['store_id']}</span>": ''?>
            </td>
          </tr>
          <tr>
            <th>職種</th>
            <td>
              <select name="employee[employment_id]">
                  <?for ($i=0; $i < count($employment); $i++) {?>
                      <option value="<?=$employment[$i]['id']?>" <?=($employee['employment_id'] == $employment[$i]['id'])? 'selected':''?>><?=$employment[$i]['employment_type']?></option>
                  <?}?>
              <select>
              <?= isset($errorMsgs['employment_id'])? "<span>{$errorMsgs['employment_id']}</span>": ''?>
            </td>
          </tr>
          <tr>
            <th>入社日</th>
            <td>
              <input type="text" class="testDatepicker" name="employee[join]" value="<?=@$employee['join']?>">
              <?= isset($errorMsgs['join'])? "<span>{$errorMsgs['join']}</span>": ''?>
            </td>
          </tr>
          <tr>
            <th>退職日</th>
            <td><input type="text" class="testDatepicker" name="employee[resign]" value="<?=@$employee['resign']?>"></td>
          </tr>
          <tr>
            <th><label for="manager_status">マーナージャ権限</label></th>
            <td><input type="checkbox" name="employee[manager]" value="" id="manager_status" <?=($employee['manager']!=0)? 'checked':''?>></td>
          </tr>
      </table>
      <input type="submit" value="保存">
      <input type="button" value="戻る" onclick="location.href='./employeeSearch.php'">
    </form>

<? require_once("require/footer.php");?>
