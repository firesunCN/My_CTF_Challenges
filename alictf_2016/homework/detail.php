<?php
include("conn.php");
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT brief FROM homework WHERE id= '" . $_GET['id'] . "'";

$result = query($sql);
if ($result) {
    $result = $result[0];
}

?>
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
<?php
if ($result) {
    echo $result["brief"];
}
?>
            </div>
        </div>
    </fieldset>
</div>

</script>
<script src="js/jquery-1.12.2.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>