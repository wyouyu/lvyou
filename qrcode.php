<?php
require_once(dirname(__FILE__) . '/app.php');
ob_get_clean();
$id = $_GET['id'];
$sec = $_GET['sec'];
$content = "券号:".$id."密码:".$sec;

Utility::QRcodeCreate($content);
