<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
header("Content-Security-Policy: default-src 'self'; script-src 'self' http://api.geetest.com; style-src 'self' http://static.geetest.com; img-src 'self' http://static.geetest.com http://dn-staticdown.qbox.me; object-src 'none'; frame-src 'none'");
header("X-Content-Security-Policy: default-src 'self'; script-src 'self' http://api.geetest.com; style-src 'self' http://static.geetest.com; img-src 'self' http://static.geetest.com http://dn-staticdown.qbox.me; object-src 'none'; frame-src 'none'");
header("X-WebKit-CSP: default-src 'self'; script-src 'self' http://api.geetest.com; style-src 'self' http://static.geetest.com; img-src 'self' http://static.geetest.com http://dn-staticdown.qbox.me; object-src 'none'; frame-src 'none'");
ini_set("session.cookie_httponly", 1);
ini_set("session.save_handler", "memcache");
ini_set("session.save_path", "tcp://127.0.0.1:11211");

session_start();

require_once("database.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Resume System</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">
        <link rel="stylesheet" href="/css/bootstrap.min.css" media="screen">
        <link rel="stylesheet" href="/css/bootswatch.min.css">
        <link rel="stylesheet" href="/css/main.css"/>
    </head>
<body>
    <div class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <div class="navbar-brand">
                    <a href="index.php">Recruitment</a></div>
            </div>
            <div class="navbar-collapse collapse" id="navbar-main">
                <ul class="nav navbar-nav">
<?php
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
?>
                    <li>
                        <a href="/admin/index.php">Admin Panel</a>
                    </li>
<?php
}
?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
<?php
if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true) {
?>
                    <li>
                        <div class="navbar-brand">
<?php
echo $_SESSION['username'];
?>
                        </div>
                    </li>
                    <li>
                        <a href="/logout.php">Sign Out</a>
                    </li>
<?php
} else {
?>
                    <li>
                        <a href="/login.php">Sign In</a>
                    </li>
                    <li>
                        <a href="/reg.php">Create Account</a>
                    </li>
<?php
}
?>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="firecms">