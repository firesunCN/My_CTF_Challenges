<?php
/**
* @author firesun 
* @website https://github.com/firesunCN
*/
require_once('header.php');
if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_ip']) || $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

if(isset($_POST["website"]) && $_POST["website"]!=="") {
    if (!preg_match("/^http:\/\/([a-zA-Z0-9.])+\/([a-zA-Z0-9]+\/)*([a-zA-Z0-9]+.jpg)$/",$_POST["website"])) {
        die('<div class="alert alert-danger">invalid URL!</div>');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$_POST["website"]);
    curl_setopt($ch, CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch, CURLOPT_TIMEOUT,10);
    ob_start();  
    $res=curl_exec ($ch);  
    $content = ob_get_contents();  
    if(stripos($content,"firesun")!==false)
        die("Be a good person!");
    ob_end_clean();  
    if(!$res)
        die('<div class="alert alert-danger">Get picture failed!</div>');
    $pics = date("YmdHis") . getRandChar(16) . ".jpg";
    $pic_path = "upload/". $pics;
    file_put_contents($pic_path,$content);
    $sql = "update user set avatar='".$pic_path."' where username='".$_SESSION['username']."';";
    $result=query($sql);    
    die('<div class="alert alert-success">Upload Success!</div>');
}
else {
    if(isset($_FILES['pic']['name']) && $_FILES['pic']['name']!=="")
    {
        $picname = $_FILES['pic']['name'];
        $picsize = $_FILES['pic']['size'];
        if ($picname != "") {
            if ($picsize > 1024000) {
                die('<div class="alert alert-danger">Too big!</div>');
            }
            $type = strstr($picname, '.');
            if ((($_FILES["pic"]["type"] != "image/jpeg") && ($_FILES["pic"]["type"] != "image/pjpeg"))) {
                die('<div class="alert alert-danger">JPG only!</div>');
            }
            $pics = date("YmdHis") . getRandChar(16) . ".jpg";
            $pic_path = "upload/". $pics;
            move_uploaded_file($_FILES['pic']['tmp_name'], $pic_path);
        }
        $sql = "update user set avatar='".$pic_path."' where username='".$_SESSION['username']."';";
        $result=query($sql);
        die('<div class="alert alert-success">Upload Success!</div>');
    }
}

function getRandChar($length) {
   $str = null;
   $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
   $max = strlen($strPol)-1;

   for($i=0;$i<$length;$i++) {
    $str.=$strPol[rand(0,$max)];
   }

   return $str;
}
?>
        <form class="bs-example form-horizontal" action="avatar.php" method="post" enctype="multipart/form-data">
            <legend>Avatar</legend>
            <ul id="uploadPanel" class="nav nav-tabs">
                <li class="active">
                    <a href="#local" data-toggle="tab">Local Image</a></li>
                <li>
                    <a href="#remote" data-toggle="tab">Internet Image</a></li>
            </ul>
            <div id="uploadContent" class="tab-content">
                <div class="tab-pane fade in active" id="local">
                    <div class="uploadPanel form-group">
                        <label class="col-lg-2 control-label">File：</label>
                        <div class="col-lg-3">
                            <input type="file" name="pic" class="form-control" id="localfile">
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="remote">
                    <div class="uploadPanel form-group">
                        <label class="col-lg-2 control-label">URL：</label>
                        <div class="col-lg-3">
                            <input type="text" name="website" class="form-control" id="internetfile">
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-3">
                    <input type="submit" name="submit" class="btn btn-primary" value="upload" />
                </div>
            </div>
        </form>
    </div>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>