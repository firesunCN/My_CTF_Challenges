<?php
include("conn.php");
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$sql    = "SELECT * FROM homework WHERE username= '" . $_SESSION['username'] . "'";
$result = query($sql);

?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1.0">
    <title>Homework System</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <div class="container">
        <fieldset>
            <div class="panel panel-success">
                <div class="panel-heading">
                    <legend>
                        Homework System
                        <a href="logout.php" class="return">Sign Out </a>
                        <br/>
                    </legend>

                </div>
            </div>
        </fieldset>
        <fieldset>
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title"><legend>Your Homework</legend></h3>
                </div>
                <div class="panel-body">
                    <ul class="list-group">
                        <li class="list-group-item"><h4>Question:</h4></li>
                        <li class="list-group-item">Hello,everyone!<br/>Now,you need to draw one beautiful picture about your first  lover !<br/>
                        Please complete this  assignments !</li>
                    </ul>
                    <div class="panel-heading">
                        <h3 class="panel-title"><legend>Upload your homework</legend></h3>
                    </div>
                    <div class="panel-body">
                        <form action="upload.php" method="post" enctype="multipart/form-data">
                            <a  class="file">Attachment
                                <input type="file" name="pic" id="file" accept="image/*" >
                            </a>
                            <textarea input type ="text" name="detail" placeholder="Please write your Answer"></textarea>
                            <p style="font-size: 0.8em; color:#9D9D9D"> a brief description about her or him</p>
                            <input type="submit" name="sub" value="Submit"  class="btn btn-success">
                        </form>
                    </div>    
                    
                    <div class="panel-heading">
                        <h3 class="panel-title"><legend>Homework List</legend></h3>
                    </div>                
                    <div class="panel-body">
                        <div class="result-content">
                            <table class="result-tab" width="100%">
                                <tr>    
                                    <th>file_id</th>
                                    <th>Action</th>
                                </tr>
<?php
foreach ($result as $row) {
    echo "<tr>";
    echo "<td>" . $row["id"] . "</td>";
    echo '<td><a class="link-update" href="detail.php?id=' . $row["id"] . '">See more detail</a></td>';
    echo "</tr>";
}
?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
	
<script src="js/jquery-1.12.2.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
