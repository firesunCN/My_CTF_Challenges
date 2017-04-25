<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
require_once("header.php");

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

if (isset($_POST['username']) && $_POST['username'] != "") {
    require_once("lib/class.geetestlib.php");
    $captcha_id = $_SESSION['captcha_id'];
    $GtSdk      = new GeetestLib("xxxxxxxxxxxxxxxxxxxxxx", "xxxxxxxxxxxxxxxxxxxxxx");
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
    
    $sql    = "select * from user where username='" . $_POST['username'] . "' and password='" . $_POST['password'] . "';";
    $result = query($sql);
    
    if ($result) {
        $result = $result[0];
    }
    if ($result) {
        $_SESSION['is_login'] = true;
        $_SESSION['is_admin'] = $result['admin'] ? true : false;
        $_SESSION['user_ip']  = $_SERVER['REMOTE_ADDR'];
        $_SESSION['username'] = $result['username'];
        header("Location: index.php");
        exit();
    } else {
        die('<div class="alert alert-danger">wrong username or password!</div>');
    }
    exit();
}
?>


        <form class="bs-example form-horizontal" action="" method="post" name="log">
            <legend>User account</legend>
            <div class="form-group">
                <label for="inputEmail" class="col-lg-2 control-label">Username：</label>
                <div class="col-lg-3">
                    <input type="text" name="username" class="form-control" id="inputEmail" required="required">
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail" class="col-lg-2 control-label">Password：</label>
                <div class="col-lg-3">
                    <input type="password" name="password" class="form-control" id="inputEmail" required="required">
                </div>
                
            </div>
            <div class="form-group">
                <label for="inputEmail" class="col-lg-2 control-label">Captcha：</label>
                <div class="col-lg-3">
                    <div  class="box" id="captcha-box"></div>    
                </div>    
            </div>
            
            <div class="form-group">
                <div><p><input type="submit" name="submit" class="btn btn-primary" value="Login"/></p></div>
            </div>
        </form>
    </div>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/get.js"></script>
<script src="js/captcha.php"></script>
