<?php
require_once("../lib/class.geetestlib.php");
$GtSdk = new GeetestLib("xxxxxxxxxxxxxxxxxxxxxxxxxx", "xxxxxxxxxxxxxxxxxxxxxxxxxx");
ini_set("session.cookie_httponly", 1);
session_start();
$captcha_id             = "captcha_110";
$status                 = $GtSdk->pre_process($captcha_id);
$_SESSION['gtserver']   = $status;
$_SESSION['captcha_id'] = $captcha_id;
echo 'var config=' . $GtSdk->response_str . ';';
?>
config['lang']='en';
var captchaObj = new Geetest(config);
captchaObj.appendTo("#captcha-box");
captchaObj.onSuccess(function () {
});
captchaObj.getValidate();  