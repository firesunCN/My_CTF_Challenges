<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */

require_once('header.php');

$result = query("select * from user;");
?>
		<div class="result-wrap">
			<div class="result-content">
				<table class="result-tab" width="100%">
					<tr>	
						<th>ID</th>
						<th>用户名</th>
						<th>邮件</th>
						<th>详情</th>
					</tr>
<?php
foreach ($result as $row) {
    echo "<tr>";
    echo "<td>" . $row["id"] . "</td>";
    echo "<th>" . $row["username"] . "</th>";
    echo "<td>" . $row["email"] . "</td>";
    echo '<td><a class="link-update" href="user.php?id=' . $row["id"] . '">查看详情</a></td>';
    echo "</tr>";
}
?>
				</table>
			</div>
		</div>
	</div>
</div>
	