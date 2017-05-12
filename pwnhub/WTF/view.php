<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
require_once('header.php');

if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true) {
    if (!isset($_SESSION['user_ip']) || $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
} else
    die("bad man!");


if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit();
}

$id = base64_decode($_GET["id"]) - 100000;

$st = $pdo->prepare("select * from blog where user =? and id=?;");
$st->bindParam(1, $_SESSION['username']);
$st->bindParam(2, $id);
$st->execute();
$result = $st->fetchAll();

$pdo = null;
if ($result) {
    $result = $result[0];
}

if (!$result) {
    exit();
}
?>
		<div class="span10">
			<div id="contents">
				<div class="page-header">
					<div class="layout">
						<aside class="layout__aside layout__aside--left">
							<label for="user">Title:</label>
								<div id="title"></div><hr>
								
							<label for="email">Content:</label>
								<div id="content"></div>
								
								<br/><br><hr>
								
						</aside>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="js/jquery.min.js"></script>
<script>
title="<?php echo addslashes($result['title']);?>";
content="<?php echo addslashes($result['article']);?>";
$("#title").html(title);
$("#content").html(content);
</script> 
