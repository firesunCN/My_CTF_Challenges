<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
require_once('header.php');

if (isset($_COOKIE['username']) && decrypt($_COOKIE['username']) !== "") {
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] !== "" && $_POST['password'] !== "") {
    if (!(substr(md5($_POST['captcha']), 0, 4) === $_SESSION['captcha']))
        die('<div class="alert alert-danger">乖乖的输验证码</div>');
    
    if (!preg_match("/^\w+$/", $_POST['username'])) {
        die('<div class="alert alert-danger">用户名只允许\w+</div>');
    }
    
    $sql    = "INSERT INTO user (username,password,email,profile) VALUES ('" . $_POST['username'] . "','" . $_POST['password'] . "','" . $_POST['email'] . "','" . $_POST['profile'] . "')";
    $result = query($sql);
    
    if ($result) {
        header('Location: login.php');
        exit();
    } else
        echo '<div class="alert alert-danger">重复的用户名</div>';
}
?>

		<form class="bs-example form-horizontal" action="reg.php" method="post" name="reg">
			<legend>注册</legend>
			<div class="form-group">
				<label class="col-lg-2 control-label">用户名：</label>
				<div class="col-lg-3">
					<input type="text" name="username" class="form-control" id="inputUsername">
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label">密码：</label>
				<div class="col-lg-3">
					<input type="password" name="password" class="form-control" id="inputPassword">
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label">邮箱：</label>
				<div class="col-lg-3">
					<input type="text" name="email" class="form-control" id="inputEmail">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-lg-2 control-label">简历：</label>
				<div class="col-lg-3">
					<textarea rows="4" type="text" name="profile" class="form-control" id="inputProfile"></textarea>
				</div>
				
			</div>
			<div class="form-group">
				<label  class="col-lg-2 control-label">验证码：</label>
					<div  class="box" id="temp-captcha-box">
<?php
$captcha             = getCaptcha(4);
$_SESSION['captcha'] = $captcha;
echo "substr(md5(captcha), 0, 4)=" . $captcha;
function getCaptcha($length) {
    $str    = null;
    $strPol = "0123456789abcdef";
    $max    = strlen($strPol) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];
    }
    
    return $str;
}
?>
					</div>
				<div class="col-lg-3">	
					<input name="captcha">	
				</div>	
			</div>
				
			<div class="form-group">
			
				<div><input type="submit" name="submit" class="btn btn-primary" value="注册"/></div>
			</div>
		</form>
	</div>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/get.js"></script>
<script src="js/captcha.php"></script>
