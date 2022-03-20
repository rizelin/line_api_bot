<?require_once('../require/header.php');?>

<div class="body">
    <div class="title_name">
        <h1>従業員エラー一覧</h1>
        <!-- <a href="./incompleteSchedule.php">スケジュールエラーページ>></a> -->
    </div>
    <div class="main_table">
        <table class="type09">
            <thead>
              <tr>
                  <th scope="cols">ユーザー名OR従業員名</th>
                  <th scope="cols">設定</th>
                  <th scope="cols">エラー内容</th>
              </tr>
            </thead>
            <tbody>
              <?for ($i=0; $i < count($line); $i++) {?>
                <tr>
                    <th scope="row"><?=$line[$i]['display_name']?></th>
                    <td><input type="button" value="編集" onclick="location.href='../employee/lineUserRelation.php?id=<?=$line[$i]['id']?>'"></td>
                    <td>従業員情報がつながっていないLineアドレスです。</td>
                </tr>
              <?}for ($i=0; $i < count($affiliation); $i++) {?>
                  <tr>
                    <th scope="row"><?=$affiliation[$i]['name']?></th>
                    <td><input type="button" value="編集" onclick="location.href='../employee/employeeManagement.php?id=<?=$affiliation[$i]['id']?>'"></td>
                    <td>削除された<?=$affiliation[$i]['error_info']?>情報が設定されています。</td>
                  </tr>
              <?}?>
            </tbody>
        </table>
    </div>
</div>
<? require_once("require/footer.php");?>
