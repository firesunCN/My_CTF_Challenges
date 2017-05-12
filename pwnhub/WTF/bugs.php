<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
require_once('header.php');

if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true) {
    if (!isset($_SESSION['user_ip']) || $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
} else {
    $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
    header("Location: login.php?redirecturl=" . urlencode($url));
    exit();
}

if (isset($_POST['url']) && $_POST['url'] !== "") {
    require_once("lib/class.geetestlib.php");
    $captcha_id = $_SESSION['captcha_id'];
    $GtSdk      = new GeetestLib("xxxxxxxxxxxxxxxxxxxxxxxxxx", "xxxxxxxxxxxxxxxxxxxxxxxxxx");
    $res        = false;
    if ($_SESSION['gtserver'] == 1) {
        $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $captcha_id);
        if ($result) {
            $res = true;
        } else {
            $res = false;
        }
    } else {
        if ($GtSdk->fail_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'])) {
            $res = true;
        } else {
            $res = false;
        }
    }
    if (!$res)
        die('<div class="alert alert-danger">You aren\'t human,are you?</div>');
    //部署时需要改掉这个ip 54.223.79.194
    if (!preg_match("/^http:\/\/54.223.79.194\/[a-zA-Z0-9_\-\/.=%?&]+$/", $_POST["url"])) {
        die('<div class="alert alert-danger">invalid URL!</div>');
    }
    
    $st = $pdo->prepare("INSERT INTO url (url) VALUES (?)");
    $st->bindParam(1, $_POST['url']);
    $result = $st->execute();
    $pdo    = null;
    
    if ($result) {
        echo '<div class="alert alert-success">Success!</div>';
        exit();
    }
    
    else
        echo '<div class="alert alert-danger">Failed!</div>';
}
?>

		<form class="bs-example form-horizontal" action="bugs.php" method="post" name="reg">
			<legend>Report Bugs</legend>
			<div class="form-group">
				<label class="col-lg-2 control-label">URL: </label>
				<div class="col-lg-3">
					<input type="text" name="url" class="form-control" id="inputUrl">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-lg-2 control-label">Captcha: </label>
				<div class="col-lg-3">
					<div  class="box" id="captcha-box"></div>	
				</div>	
			</div>
				
			<div class="form-group">
				<div><input type="submit" name="submit" class="btn btn-primary" value="submit"/></div>
			</div>
		</form>
	</div>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/get.js"></script>
<script src="js/captcha.php"></script>
