<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
require_once("header.php");

if (isset($_COOKIE['username']) && decrypt($_COOKIE['username']) !== "") {
    header("Location: index.php");
    exit();
}

if (isset($_POST['username']) && $_POST['username'] != "") {
    if (!(substr(md5($_POST['captcha']), 0, 4) === $_SESSION['captcha']))
        die('<div class="alert alert-danger">乖乖的输验证码</div>');
    
    if (!preg_match("/^\w+$/", $_POST['username'])) {
        die('<div class="alert alert-danger">用户名只允许\w+</div>');
    }
    
    $sql = "select * from user where username='" . $_POST['username'] . "' and password='" . $_POST['password'] . "';";
    
    $result = query($sql);
    
    if ($result) {
        $result = $result[0];
    }
    if ($result) {
        setcookie("username", encrypt($result['username']));
        header("Location: index.php");
        exit();
    } else {
        die('<div class="alert alert-danger">错误的用户名或者密码</div>');
    }
    exit();
}
?>


        <form class="bs-example form-horizontal" action="" method="post" name="log">
			<legend>登录</legend>
			<div class="form-group">
				<label for="inputEmail" class="col-lg-2 control-label">用户名：</label>
				<div class="col-lg-3">
					<input type="text" name="username" class="form-control" id="inputEmail" required="required">
				</div>
			</div>
			<div class="form-group">
				<label for="inputEmail" class="col-lg-2 control-label">密码：</label>
				<div class="col-lg-3">
					<input type="password" name="password" class="form-control" id="inputEmail" required="required">
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
				<div><p><input type="submit" name="submit" class="btn btn-primary" value="登录"/></p></div>
			</div>
		</form>
	</div>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/get.js"></script>
<script src="js/captcha.php"></script>
