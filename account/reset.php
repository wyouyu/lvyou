<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if(isset($_SESSION['user_id'])) {
	unset($_SESSION['user_id']);
	ZLogin::NoRemember();
	$login_user = $login_user_id = $login_manager = $login_leader = null;
}

$code = strval($_GET['code']);
if ( $code == 'ok' && is_get()) {
	die(include template('account_reset_ok'));
}

$user = Table::Fetch('user', $code, 'recode');
if (!$user) {
	Session::Set('error', '重设密码的链接无效');
	redirect( WEB_ROOT . '/index.php');
}

if (is_post()) {
	if ($_POST['password'] == $_POST['password2']) {
		ZUser::Modify($user['id'], array(
			'password' => $_POST['password'],
			'recode' => '',
		));
		redirect( WEB_ROOT . '/account/reset.php?code=ok');
	}
	Session::Set('error', '两次输入的密码不匹配，请重新设置');
}

$pagetitle = '重设密码';
include template('account_reset');
