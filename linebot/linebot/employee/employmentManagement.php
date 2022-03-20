<?php
require_once('../require/mysql.php');
require_once('../require/login.php');

//고용형태 불러와서 보여주고 입력 수정 삭제
$sql = "SELECT `id`,`employment_type` FROM `employment` WHERE `status`=0";
if ($res = $_link->query($sql)) {
  while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
    $employment[] = $row;
  }
  $cnt = count($employment);
}

if (isset($_POST['employment'])) {
  $employments = $_POST['employment'];
  $allowColumns = array('id','employment_type');

  foreach ($employments as $key => $value) {
    if (!in_array($key,array_keys($allowColumns))) {
      $error = "もう一度入力してください。";
    }
  }

  if (!isset($error)) {
    $employments['employment_type'] = array_values(array_filter(array_map('trim',array_unique($employments['employment_type']))));
    $updateCnt = count($employments['id']);
    //아이디의 길이만큼 돌면서 업데이트, 타입이 없다면 아이디를 삭제로 업데이트
    for ($i=0; $i < $updateCnt; $i++) {
      if (isset($employments['employment_type'][$i])) {
        $sqlFlg = FALSE;
        $sql = "UPDATE `employment` SET `employment_type` = '{$employments["employment_type"][$i]}' WHERE `id` = '{$employments["id"][$i]}'";
        if ($res = $_link->query($sql)) {
          $sqlFlg = TRUE;
          unset($employments['employment_type'][$i]);
        }
      }else {
        $sqlFlg = FALSE;
        $sql = "UPDATE `employment` SET `status` = 1 WHERE `id` = '{$employments["id"][$i]}'";
        if ($res = $_link->query($sql)) {
          $sqlFlg = TRUE;
          unset($employments['employment_type'][$i]);
        }
      }
    }

    if (!empty($employments['employment_type'])) {
    $sqlFlg = FALSE;
      foreach ($employments['employment_type'] as $key => $value) {
        $insertVal[] = "('{$value}')";
      }
      $insertVal = implode(',',$insertVal);
      $sql = "INSERT INTO `employment` (`employment_type`) VALUES {$insertVal}";
      if ($res = $_link->query($sql)) {
        $sqlFlg = TRUE;
      }
    }

    if ($sqlFlg) {
      $_link->commit();
      $msg = "保存しました。";
    }else {
      $_link->rollback();
      $msg = "エラーがあります。";
    }
  }
}else {
  $msg = $error;
}
require_once("./employmentManagement.tpl.php");
?>


<?=(isset($msg))? "<p>$msg</p>":''?>
<form method="post" action="./employmentManagement.php">
  <table class="type09">
      <thead>
      <tr>
          <th scope="cols">No</th>
          <th scope="cols">雇用区分</th>
      </tr>
      </thead>
      <tbody id="employment">
        <?for ($i=0; $i < $cnt; $i++) {?>
          <input type="hidden" name="employment[id][]" value="<?=$employment[$i]['id']?>">
        <tr id="employment<?=$i?>">
            <th scope="row"><?=$i+1?></th>
            <td class="value_td"><?=$employment[$i]['employment_type']?></td>
            <td class="input_td" style="display:none"><input type="text" name="employment[employment_type][]" value="<?=$employment[$i]['employment_type']?>"></td>
        </tr>
        <?}?>
      </tbody>
  </table>
  <input type="button" id="emp_modify" value="修正">
  <div id="emp_button" style="display:none">
      <input type="hidden" value="<?=$cnt-1?>" id="emp_val">
      <input type="button" id="emp_addInput" value="追加">
      <input type="button" id="emp_delInput" value="削除">
      <input type="submit" value="登録">
  </div>
</form>

<script>
  $('#emp_modify').click(function(){
      $('.value_td').css('display','none');
      $('.input_td').css('display','block');
      $(this).css('display','none');
      $('#emp_button').css('display','block');
  });

  $('#emp_addInput').click(function(){
    var num = $('#emp_val').val();
    num++;
    $('#employment').append( $('<tr/>',{id:'employment'+num}) );
    $('#employment'+num).append( $('<th/>',{scope:'row',text: num+1}), $('<td/>',{class:'input_id'}) );
    $('#employment'+num+' td').append( $('<input/>',{type:'text',name:'employment[employment_type][]'}) );
    $('#emp_val').val(num);
  });

  $('#emp_delInput').click(function(){
    var num = $('#emp_val').val();
    if (num > 0) {
      $('#employment'+num).remove();
      num--;
      $('#emp_val').val(num);
    }
  });
</script>
