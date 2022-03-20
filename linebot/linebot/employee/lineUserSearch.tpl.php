<?require_once('../require/header.php');?>

<div class="body">
    <div class="title_name">
        <h1>LINEユーザー一覧</h1>
    </div>
    <div class="main_table">
        <table class="type09">
            <thead>
            <tr>
                <th scope="cols">Lineユーザー名</th>
                <th scope="cols">従業員</th>
                <th scope="cols">設定</th>
            </tr>
            </thead>
            <tbody>
              <?for ($i=0; $i < count($user); $i++) {?>
              <tr class="info">
                  <th scope="row"><?="(".$user[$i]['id'].")".$user[$i]['display_name']?></th>
                  <td class="name<?=$i?>"><?=$user[$i]['name']?></td>
                  <td><input type="button" value="情報設定" onclick="location.href='./lineUserRelation.php?id=<?=$user[$i]['id']?>'"></td>
              </tr>
              <?}?>
            </tbody>
        </table>
    </div>
</div>
<script>
$(document).ready(function(){
    var leng = $('tbody tr').length;
    for (var i = 0; i < leng; i++) {
       if ($('.name'+i).text()=='') {
          $('.name'+i).css('background-color','rgb(255, 217, 217)');
       }
    }
});
</script>
