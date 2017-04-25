<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */
require_once('header.php');
//if($_SERVER['REMOTE_ADDR']!=="127.0.0.1")
die('<div class="alert alert-danger">It\'s a serect! Only local user could access</div>');
//$result=query("select content from memo;")[0];
//echo $result["content"];
