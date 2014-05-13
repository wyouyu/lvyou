<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if (is_post()) {
	$user = Table::Fetch('user', strval($_POST['email']), 'email');
	
	if ( $user ) {
		$user['recode'] = $user['recode'] ? $user['recode'] : md5(json_encode($user));
		Table::UpdateCache('user', $user['id'], array(
			'recode' => $user['recode'],
		));
		
		mail_repass($user);
		//exit(print_r('12'));
		Session::Set('reemail', $user['email']);
		echo "<script>alert('发送成功，请注意查收！')</script>";
	}
	//Session::Set('error', '你的Email没有在本站注册');
	showmessage('发送成功！','/account/repass.php');
}

$action = strval($_GET['action']);
if ( $action == 'ok') {
	die(include template('account_repass_ok'));
}
$pagetitle = '重设密码';
include template('account_repass');
