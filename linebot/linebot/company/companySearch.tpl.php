<?require_once('../require/header.php');?>
<div class="body">
    <div class="title_name">
        <h1>組織一覧</h1>
    </div>
    <input type="button" value="新規登録" onclick="location.href='./companyManagement.php'">
    <div class="main_table">
        <table class="type11">
          <thead>
            <tr>
              <th scope="cols">NO</th>
              <th scope="cols">所属</th>
              <th scope="cols">部署</th>
              <th scope="cols">店舗</th>
              <th scope="cols">編集</th>
              <th scope="cols">削除</th>
            </tr>
          </thead>
          <tbody>
            <?for ($i=0; $i < $cnt; $i++) {?>
              <tr id="company<?=$i+1?>">
                <td><?=$i+1?></td>
                <td id="company_name<?=$i+1?>"><?=$company[$i]['company_name']?></td>
                <td class="sub"><?=$company[$i]['department']?></td>
                <td class="sub"><?=$company[$i]['store']?></td>
                <td><input type="button" value="管理" onclick="location.href='./companyManagement.php?id=<?=$company[$i]['id']?>'"></td>
                <td><input type="button" value="削除" onclick="delecteCompany(<?=$company[$i]['id']?>,<?=$i+1?>);"></td>
              </tr>
            <?}?>
          </tbody>
        </table>
    </div>
</div>

<script>

//Company Search
function delecteCompany(id,num){
  var company = $('#company_name'+num).text();
  if (confirm(company+'を消しますか？')) {
    $.ajax({
      type: 'POST',
      url:'../company/company.php',
      data: {delcompany:id},
      success: function(result){
        $('#company'+num).remove();
      },
      error: function(){
        alert("処理中エラーが発生しました。");
      }
    });
  }
}
</script>
