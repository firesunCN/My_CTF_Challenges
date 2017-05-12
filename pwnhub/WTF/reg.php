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
    header("Location: index.php");
    exit();
}

if (isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] !== "" && $_POST['password'] !== "") {
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
    
    $st = $pdo->prepare("INSERT INTO user (username,password) VALUES (?,?)");
    
    $st->bindParam(1, $_POST['username']);
    $st->bindParam(2, $_POST['password']);
    
    $result = $st->execute();
    $pdo    = null;
    
    if ($result) {
        header('Location: login.php');
        exit();
    }
    
    else
        echo '<div class="alert alert-danger">Register Failed: maybe duplicate username!</div>';
}
?>

		<form class="bs-example form-horizontal" action="reg.php" method="post" name="reg">
			<legend>Register</legend>
			<div class="form-group">
				<label class="col-lg-2 control-label">Username: </label>
				<div class="col-lg-3">
					<input type="text" name="username" class="form-control" id="inputUsername">
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label">Password: </label>
				<div class="col-lg-3">
					<input type="password" name="password" class="form-control" id="inputPassword">
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
