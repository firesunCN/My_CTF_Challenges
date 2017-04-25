<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
require_once('header.php');
$result = query("select * from user limit 0,100;");
?>
        <div class="result-wrap">
            <div class="result-content">
                <table class="result-tab" width="100%">
                    <tr>    
                        <th>Number</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
<?php
foreach ($result as $row) {
    echo "<tr>";
    echo "<td>" . $row["id"] . "</td>";
    echo "<th>" . $row["username"] . "</th>";
    echo "<td>" . $row["email"] . "</td>";
    echo '<td><a class="link-update" href="user.php?id=' . $row["id"] . '">See more detail</a></td>';
    echo "</tr>";
}
?>
                </table>
            </div>
        </div>
    </div>
</div>
    