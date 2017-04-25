<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
require_once("header.php");

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

/*
Attention:
$_SESSION['username'] didn't be filtered. It would cause a sqli.
But it's not my original intention.
This challenge should use "HTTP Parameter Pollution" to solve. If somebody use this unintended way, the challenge would be easier.
*/
$sql = "select * from user where username='" . $_SESSION['username'] . "';";

$result = query($sql);
if ($result) {
    $result = $result[0];
}

if (!$result) {
    header("Location: logout.php");
    exit();
}
?>
        <div class="span10">
            <div id="content">
                <div class="page-header">
                    <div class="layout">
                        <aside class="layout__aside layout__aside--left">
                            <label for="user">Username:</label><?php echo $result['username'];?><hr>
                            <label for="email">Email:</label><?php echo $result['email'];?><br/><br><hr>
                            <label for="profile">Profile:</label><br>
                            <div id=comment name="comment"><?php echo $result['profile'];?><br/></div>
                        </aside>
                        <aside class="layout__aside layout__aside--right"><a href="avatar.php"><img src="<?php echo $result['avatar'];?>" width="200" height="200" class="img-thumbnail" ></a></aside>
                        <br/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

