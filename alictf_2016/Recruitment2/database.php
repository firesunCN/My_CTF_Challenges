<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
require_once("waf.php");
function query($sql) {
    $host     = 'localhost';
    $user     = 'fire';
    $pass     = 'fire';
    $database = 'firecms';
    $con      = mysql_connect($host, $user, $pass);
    if (!$con)
        die('Could not connect: ' . mysql_error());
    mysql_select_db($database, $con);
    $result = mysql_query($sql);
    if ($result === true || $result === false) {
        mysql_close($con);
        return $result;
    }
    $res = array();
    do {
        $row = mysql_fetch_array($result);
        if ($row)
            $res[] = $row;
    } while ($row);
    mysql_close($con);
    return $res;
}