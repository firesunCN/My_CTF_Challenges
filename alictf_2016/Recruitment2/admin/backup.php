<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
ini_set("session.cookie_httponly", 1);
ini_set("session.save_handler", "memcache");
ini_set("session.save_path", "tcp://127.0.0.1:11211");
session_start();

if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] !== true) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['user_ip']) || $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("You are not an administrator!");
}

$file_name = "/tmp/backup.zip";
$file      = fopen($file_name, "r");
Header("Content-type: application/octet-stream");
Header("Accept-Ranges: bytes");
Header("Accept-Length: " . filesize($file_name));
Header("Content-Disposition: attachment; filename=" . $file_name);
echo fread($file, filesize($file_dir . $file_name));
fclose($file);
exit();

?> 
