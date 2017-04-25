<?php
include("conn.php");
if (isset($_POST["submit"])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if (!preg_match("/^\w+$/", $username) || !preg_match("/^\w+$/", $username))
        die("Only allow [\w+]!");
    if (!isset($username) || $username === "" || !isset($password) || $password === "") {
        die("error");
    } else {
        $sql    = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
        $result = query($sql);
        if ($result) {
            $result = $result[0];
        }
        
        if ($result) {
            $_SESSION['username'] = $result['username'];
            header("Location: index.php");
            exit();
        } else {
            die('<div class="alert alert-danger">wrong username or password!</div>');
        }
        exit();
    }
}
?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1.0">
    <title>Login</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>


<body>

    <div class="container">
        <div class="login">
            <form method="POST"  class="form-horizontal">
                <h3>Login</h3>
                <div class="form-group">
                    <label class="sr-only" for="inputEmail">Username</label>
                    <div class="input-group">
                        <div class="input-group-addon"><samp class="glyphicon glyphicon-user"></samp></div>
                        <input type="username" name="username" class="form-control" id="inputEmail" placeholder="Username">
                    </div>
                </div>
                <div class="form-group">
                    <label class="sr-only" for="inputEmail">Password</label>
                    <div class="input-group">
                        <div class="input-group-addon"><samp class="glyphicon glyphicon-lock"></samp></div>
                        <input type="password" name="password" class="form-control" id="inputPassword3" placeholder="Password">
                    </div>
                </div>
                <div class="form-group ">
                    <label class="sr-only" for="inputSubmit">submit</label>
                    <input type="submit" name="submit" class="btn btn-primary" id="inputSubmit" value="Login">
                </div>
                <div class="form-group">
                    <label class="sr-only" for="register">register</label>
                    <button type="button" class="btn btn-link" id="register"><a href="register.php">Not yet registered? Sign up now</a></button>
                </div>
            </form>
        </div>
    </div>
    <script src="js/jquery-1.12.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>

</html>

