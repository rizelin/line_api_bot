<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="../common/css/reset.css">
    <link rel="stylesheet" href="../common/css/e_style.css">
    <link rel="stylesheet" href="../common/jqueryUI/jquery-ui.css">
    <link rel="stylesheet" href="../common/jqueryUI/MonthPicker.min.css">
    <link rel="stylesheet" href="../common/jqueryUI/jquery-ui-timepicker-addon.css">
    <script src="../common/js/jquery-3.3.1.js"></script>
    <script src="../common/jqueryUI/jquery-ui.js"></script>
    <script src="../common/jqueryUI/MonthPicker.min.js"></script>
    <script src="../common/jqueryUI/jquery-ui-timepicker-addon.js"></script>
    <script src="../common/js/js.js"></script>

    <nav class="header_nav">
      <ul id="navi">
        <li><a href="<?= isset($user)?'../attendance/dailyAttendance.php':''?>">データ</a></li>
        <li><a href="<?= isset($user)?'../employee/employeeSearch.php':''?>">社員検索</a></li>
        <li><a href="<?= isset($user)?'../company/companySearch.php':''?>">組織一覧</a></li>
        <li><a href="<?= isset($user)?'../employee/lineUserSearch.php':''?>">Lineユーザー一覧</a></li>
        <li><a href="<?= isset($user)?'../incomplete/incompleteEmployee.php':''?>">エラー処理</a></li>
        <li><a href="<?= isset($user)?'../incomplete/incompleteSchedule.php':''?>">エラー一覧</a></li>
        <?= isset($user)?'<li><a href="../require/login_interface.php?logout">ログアウト</a></li>':''?>
      </ul>
  </nav>
  </head>
  <body>
