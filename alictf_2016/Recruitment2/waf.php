<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
function waf($str) {
    if (stripos($str, "select") !== false)
        die("Be a good person!");
    if (stripos($str, "union") !== false)
        die("Be a good person!");
    if (stripos($str, "from") !== false)
        die("Be a good person!");
    if (stripos($str, "where") !== false)
        die("Be a good person!");
    if (stripos($str, "insert") !== false)
        die("Be a good person!");
    if (stripos($str, "update") !== false)
        die("Be a good person!");
    if (stripos($str, "delete") !== false)
        die("Be a good person!");
    if (stripos($str, "../") !== false)
        die("Be a good person!");
    if (stripos($str, "..\\") !== false)
        die("Be a good person!");
    if (stripos($str, "'") !== false)
        die("Be a good person!");
    if (stripos($str, '"') !== false)
        die("Be a good person!");
    if (stripos($str, "load_file") !== false)
        die("Be a good person!");
    if (stripos($str, "outfile") !== false)
        die("Be a good person!");
    if (stripos($str, "execute") !== false)
        die("Be a good person!");
    if (stripos($str, "#") !== false)
        die("Be a good person!");
    if (stripos($str, "--") !== false)
        die("Be a good person!");
    if (stripos($str, "eval") !== false)
        die("Be a good person!");
    if (stripos($str, "\\") !== false)
        die("Be a good person!");
    if (stripos($str, "(") !== false)
        die("Be a good person!");
    if (stripos($str, ")") !== false)
        die("Be a good person!");
    if (stripos($str, "=") !== false)
        die("Be a good person!");
    if (stripos($str, "*") !== false)
        die("Be a good person!");
    if (stripos($str, "`") !== false)
        die("Be a good person!");
    if (stripos($str, "&") !== false)
        die("Be a good person!");
}

function wafArr($arr) {
    foreach ($arr as $k => $v) {
        waf($k);
        waf($v);
    }
}

wafArr($_GET);
wafArr($_POST);
wafArr($_COOKIE);

function stripStr($str) {
    if (get_magic_quotes_gpc())
        $str = stripslashes($str);
    return addslashes(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));
}

$uri = explode("?", $_SERVER['REQUEST_URI']);
if (isset($uri[1])) {
    $parameter = explode("&", $uri[1]);
    foreach ($parameter as $k => $v) {
        $v1 = explode("=", $v);
        if (isset($v1[1])) {
            $_REQUEST[$v1[0]] = stripStr($v1[1]);
        }
    }
}

function stripArr($arr) {
    $new_arr = array();
    foreach ($arr as $k => $v) {
        $new_arr[stripStr($k)] = stripStr($v);
    }
    return $new_arr;
}

$_GET    = stripArr($_GET);
$_POST   = stripArr($_POST);
$_COOKIE = stripArr($_COOKIE);