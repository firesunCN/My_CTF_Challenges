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
} else {
    $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
    header("Location: login.php?redirecturl=" . urlencode($url));
    exit();
}

if (isset($_POST['title']) && isset($_POST['content']) && $_POST['title'] !== "" && $_POST['content'] !== "") {
    $st      = $pdo->prepare("INSERT INTO blog (title,article,user) VALUES (?,?,?)");
    $title   = $_POST['title'];
    $content = $_POST['content'];
    
    $title   = str_replace("<", "", $title);
    $title   = str_replace(">", "", $title);
    $title   = str_replace('"', "", $title);
    $title   = str_replace("'", "", $title);
    $content = str_replace("<", "", $content);
    $content = str_replace(">", "", $content);
    $content = str_replace('"', "", $content);
    $content = str_replace("'", "", $content);
    
    $st->bindParam(1, $title);
    $st->bindParam(2, $content);
    $st->bindParam(3, $_SESSION['username']);
    $result = $st->execute();
    $id     = $pdo->lastInsertId();
    $pdo    = null;
    die('<div class="alert alert-success">Success! <a href="view.php?id=' . urlencode(base64_encode($id + 100000)) . '">detail</a></div>');
    
}
?>
		<form class="bs-example form-horizontal" action="new.php" method="post" name="new">
			<legend>New Article</legend>
			<div class="form-group">
				<label class="col-lg-2 control-label">Title: </label>
				<div class="col-lg-3">
					<input type="text" name="title" class="form-control" id="inputTitle">
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label">Content: </label>
				<div class="col-lg-3">
					<textarea type="text" name="content" class="form-control" id="inputContent"></textarea>
				</div>
			</div>
				
			<div class="form-group">
				<div><input type="submit" name="submit" class="btn btn-primary" value="submit"/></div>
			</div>
		</form>
	</div>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/get.js"></script>
<script src="js/captcha.php"></script>
