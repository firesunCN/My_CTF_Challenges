<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
ini_set("session.save_handler", "memcache");
ini_set("session.save_path", "tcp://127.0.0.1:11211");
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();