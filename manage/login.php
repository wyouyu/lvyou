<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if ( $_POST ) {
	$login_admin = ZUser::GetLogin($_POST['username'], $_POST['password']);
	if ( !$login_admin || $login_admin['manager'] != 'Y' ) {
		Session::Set('error', '用户名密码不匹配！');
		redirect( WEB_ROOT . '/manage/login.php');
	} else {
		Session::Set('admin_id', $login_admin['id']);
		Session::Set('user_id', $login_admin['id']);
		redirect( WEB_ROOT . '/manage/index.php');
	}
}

include template('manage_login');
