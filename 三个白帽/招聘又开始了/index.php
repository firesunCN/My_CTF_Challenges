<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
require_once("header.php");

if (!isset($_COOKIE['username']) || decrypt($_COOKIE['username']) === "") {
    setcookie("username");
    header("Location: login.php");
    exit();
}

$sql    = "select * from user where username='" . decrypt($_COOKIE['username']) . "';";
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
							<label for="user">用户名:</label><?php echo $result['username'];?><hr>
							<label for="email">邮箱:</label><?php echo $result['email'];?><br/><br><hr>
							<label for="profile">简历:</label><br>
							<div id=comment name="comment"><?php echo $result['profile'];?><br/></div>
						</aside>
						
						<br/>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

