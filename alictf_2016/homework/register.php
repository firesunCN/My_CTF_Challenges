<?php
include("conn.php");

if (isset($_POST["submit"])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (!preg_match("/^\w+$/", $username) || !preg_match("/^\w+$/", $username))
        die("Only allow [\w+]!");
    
    if (!isset($username) || $username === "" || !isset($password) || $password === "") {
        die("register error");
    }
    
    $sql = "INSERT INTO user(username,password) values('$username','$password')";
    if (query($sql) === true) {
        die("Success!");
    } else {
        die("Username already exists!");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/register.css">
<body>
    <div class="container">
        <div class="form">
            <span class="success"></span>
            <form method="POST" class="form-horizontal">
                <h2>Register</h2>
                <div class="form-group ">
                    <label>Username</label>
                    <input type="txt" class="form-control"  name="username" placeholder="Username"/>
                </div>
                <div class="form-group">
                  <label for="registerPwd">Password</label>
                  <input type="password" class="form-control" name="password" id="registerPwd" placeholder="Password" >
                </div>
                <div class="form-group ">
                    <label class="sr-only" for="registerSubmit">submit</label>
                    <input type="submit" name="submit" class="btn btn-primary" id="registerSubmit" value="Regester">
                </div>
                <a href="login.php" >Back</a>
                <span></span>
            </form>
        </div>
    </div>
<script src="js/jquery-1.12.2.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
