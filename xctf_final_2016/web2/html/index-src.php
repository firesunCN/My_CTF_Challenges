<?php
if (isset($_POST["url"]) && $_POST["url"] != "") {
    $res = curl($_POST["url"]);
    if (preg_match('/^http[s]?:\/\/' . '([0-9a-zA-Z\.\/]+)*$/', $_POST["url"]) == 1) {
        echo '<img src="data:image/png;base64,' . urlencode($res) . '"/>';
    }
    exit();
}

echo '<html><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width,initial-scale=1"><title>';
echo isset($_GET["title"]) ? $_GET["title"] : "Go!";
echo '</title><link href="bootstrap.min.css" rel="stylesheet"><link href="css/signin.css" rel="stylesheet"></head><body><div class="container"><div class="col-sm-12 col-md-10 col-md-push-2"><form method="post" class="js-search-form _searchtop"><div class="row"><h2 style="margin-left:2vw" class="form-signin-heading">INPUT URL:</h2><div class="col-sm-12 col-md-7"><input type="search" class="form-control js-address-search" placeholder="URL" name="url"></div><div class="col-sm-12 col-md-3"><button class="btn btn-success">search</button></div></div></form></div></div></body></html>';

