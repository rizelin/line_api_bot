<?require_once('../require/header.php');?>

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
