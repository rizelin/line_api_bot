<?require_once('../require/header.php');?>

<div class="body">
    <div class="title_name">
        <h1>ログイン</h1>
    </div>
    <form class="login_form" action="./login_interface.php" method="post">
        <input type="text" name="id" placeholder="ID">
        <input type="password" name="password" placeholder="暗証番号">
        <input type="submit" value="ログイン">
    </form>
</div>
<?require_once('../require/footer.php');?>
