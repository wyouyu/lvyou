<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

($email = Session::Get('unemail')) || ($email = $login_user['email']);
$wwwlink = mail_zd($email);

$pagetitle = '注册成功';
include template('account_signuped');
