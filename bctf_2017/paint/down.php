<?php
if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1")
    die("Localhost is not OK! ");
if (isset($_POST['data'])) {
    header('content-type:image/png');
    header('content-Disposition:attachment;filename="firesun.png"');
    echo base64_decode($_POST['data']);
}
