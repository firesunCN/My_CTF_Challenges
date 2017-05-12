<?php
/**
 * @author firesun 
 * @website https://github.com/firesunCN
 */

$pdo = new pdo('mysql:host=127.0.0.1;port=3306;dbname=fireguestbook;charset=gbk', 'fire', 'fire');
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
