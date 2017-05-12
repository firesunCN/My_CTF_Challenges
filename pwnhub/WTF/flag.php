<?php
ini_set("session.cookie_httponly", 1);
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' http://api.geetest.com; style-src 'self' http://static.geetest.com; img-src 'self' http://static.geetest.com http://dn-staticdown.qbox.me; object-src 'none'; frame-src 'none'");
header("X-Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' http://api.geetest.com; style-src 'self' http://static.geetest.com; img-src 'self' http://static.geetest.com http://dn-staticdown.qbox.me; object-src 'none'; frame-src 'none'");
header("X-WebKit-CSP: default-src 'self'; script-src 'self' 'unsafe-inline' http://api.geetest.com; style-src 'self' http://static.geetest.com; img-src 'self' http://static.geetest.com http://dn-staticdown.qbox.me; object-src 'none'; frame-src 'none'");

header("Content-Type:text/html; charset=GBK");
session_start();

if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true) {
    if (!isset($_SESSION['user_ip']) || $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

if ($_SESSION['username'] !== "firesun")
    die("Only firesun could read the flag.");

?>
flag{P1uZzUCVFeAauwEmGM7Qt8GAwtBtUtqF}