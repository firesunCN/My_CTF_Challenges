<?php
session_start();
function query($sql) {
    $host     = 'localhost';
    $user     = 'fire';
    $pass     = 'fire';
    $database = 'firecms';
    $con      = mysqli_connect($host, $user, $pass, $database);
    if (mysqli_connect_errno($con))
        die('Could not connect: ' . mysqli_connect_error());
    
    $result = mysqli_query($con, $sql);
    
    if ($result === true || $result === false) {
        mysqli_close($con);
        return $result;
    }
    $res = array();
    do {
        $row = mysqli_fetch_array($result);
        if ($row)
            $res[] = $row;
    } while ($row);
    mysqli_close($con);
    return $res;
}

include_once "lib.php";
