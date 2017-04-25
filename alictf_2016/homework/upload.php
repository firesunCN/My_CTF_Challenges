<?php
include("conn.php");
if (!isset($_POST["detail"]))
    die();

$detail = $_POST["detail"];
if (!preg_match("/^\w+$/", $detail))
    die("Only allow [\w+]!");

if (isset($_FILES['pic']['name']) && $_FILES['pic']['name'] !== "") {
    $picname = $_FILES['pic']['name'];
    if (!preg_match("/^[\w.]+$/", $picname))
        die("Filename only allow /^[\w.]+$/");
    
    $picsize = $_FILES['pic']['size'];
    if ($picname != "") {
        if ($picsize > 1024000) {
            die('Too big!');
        }
        
        if (stripos($picname, "php") !== false)
            file_put_contents($_FILES['pic']['tmp_name'], "bad man!");
        
        $pics     = date("YmdHis") . "-" . $picname;
        $pic_path = "upload/" . $pics;
        move_uploaded_file($_FILES['pic']['tmp_name'], $pic_path);
        
        $sql = "INSERT INTO homework(username,brief) values('" . $_SESSION['username'] . "','$detail')";
        query($sql);
        echo "Upload Success！Path:" . $pic_path;
    } else
        die("Where is your homework?");
} else
    die("Where is your homework?");

function getRandChar($length) {
    $str    = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max    = strlen($strPol) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];
    }
    
    return $str;
}