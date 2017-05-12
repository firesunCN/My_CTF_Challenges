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

$st = $pdo->prepare("select * from blog where user =? ORDER BY id DESC limit 0,100;");
$st->bindParam(1, $_SESSION['username']);
$st->execute();

$result = $st->fetchAll();
$pdo    = null;
?>
		<div class="form-group">
			<div><a class="btn btn-primary" href="new.php">New</a></div>
		</div>
			
		<div class="result-wrap">
			<div class="result-content">
				<table class="result-tab" width="100%">
					<tr>	
						<th>Title</th>
						
						<th>Action</th>
					</tr>
<?php
foreach ($result as $row) {
    echo "<tr>";
    echo "<td>" . $row["title"] . "</td>"; 
    echo '<td><a class="link-update" href="view.php?id=' . urlencode(base64_encode($row["id"] + 100000)) . '">See more detail</a></td>';
    echo "</tr>";
}
?>
				</table>
			</div>
		</div>
	</div>
</div>