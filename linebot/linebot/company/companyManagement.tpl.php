<?require_once('../require/header.php');?>
  <form method="post" action="./companyManagement.php">
      <table class="type02">
        <input type="hidden" name="company[id]" value="<?=$company['id']?>">
        <tr>
          <th>会社</th>
          <td>
            <input type="text" name="company[company_name]" value="<?=@$company['company_name']?>">

            <?= isset($errorMsgs['company_name'])? "<span>{$errorMsgs['company_name']}</span>": ''?>
          </td>
        </tr>
        <tr>
          <th>部署</th>
          <td id="department">
              <?if (empty($company['department'])) {?>
                  <input type="text" class="department0" name="company[department][insert][]" value="<?=@$company['department'][0]?>">
              <?}else {?>
                <input type="hidden" class="department_id" name="company[department_id]" value="<?=@$company['department_id']?>">
                  <?for ($i=0; $i < count($company['department']['update']); $i++) {?>
                    <input type="text" class="department<?=$i?>" name="company[department][update][<?=$i?>]" value="<?=@$company['department']['update'][$i]?>">
                <?}?>
              <?}?>
              <input type="button" id="d_plus" value="+" data-cnt="<?=(isset($dCnt))?$dCnt-1:0?>" onclick="addInput('department_cnt');">
              <input type="button" value="-" onclick="delInput('department_cnt');">
              <?= isset($errorMsgs['department'])? "<span>{$errorMsgs['department']}</span>": ''?>
          </td>
        </tr>
        <tr>
          <th>店舗</th>
          <td id="store">
              <?if (empty($company['store'])) {?>
                  <input type="text" class="store0" name="company[store][insert][]" value="<?=@$company['store'][0]?>">
              <?}else {?>
                <input type="hidden" class="store_id" name="company[store_id]" value="<?=@$company['store_id']?>">
                <?for ($i=0; $i <  count($company['store']['update']); $i++) {?>
                  <input type="text" class="store<?=$i?>" name="company[store][update][<?=$i?>]" value="<?=@$company['store']['update'][$i]?>">
                  <?}
              }?>
              <input type="button" value="+" id="s_plus" data-cnt="<?=(isset($sCnt))?$sCnt-1:0?>" onclick="addInput('store_cnt');">
              <input type="button" value="-" onclick="delInput('store_cnt');">
              <?= isset($errorMsgs['store'])? "<span>{$errorMsgs['store']}</span>": ''?>
          </td>
        </tr>
          <table>
          <input type="submit" value="登録">
          <input type="button" value="戻る" onclick="location.href='./companySearch.php'">
      </form>
  </body>
  <script>
  var classNum = 0;
function addInput(type){

  if (type == 'department_cnt') {
    classNum = $('#d_plus').data('cnt');
    classNum++;
    $('#department').append(
        $('<input/>',
        { name: 'company[department][insert][]',
          class: 'department'+classNum
     }));
     $('#d_plus').data('cnt',classNum);

  }else {
    classNum = $('#s_plus').data('cnt');
    classNum++;

    $('#store').append(
        $('<input/>',
        { name: 'company[store][insert][]',
          class: 'store'+classNum
     }));
     $('#s_plus').data('cnt',classNum);
  }
}

function delInput(type){
  if (type == 'department_cnt') {
    classNum = $('#d_plus').data('cnt');

    if(classNum >= 0){
            $('.department'+classNum).remove();
            classNum--;
            $('#d_plus').data('cnt',classNum);
    }

  }else {
    classNum = $('#s_plus').data('cnt');

    if(classNum >= 0){
            $('.store'+classNum).remove();
            classNum--;
            $('#s_plus').data('cnt',classNum);
        }
  }
}

  </script>
