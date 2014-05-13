<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$pagetitle = '邀请有奖';
$user = Table::Fetch('user',$login_user_id);
$mail = uencode($user['email']);
if (! is_login() ) {
	die(include template('account_invite_signup'));
}

if($_POST['recipients'] && $_POST['invitation_content']) {
	$emails = $_POST['recipients'];
	($name = $_POST['real_name']) || ($name = $login_user['username']);
	$content = $_POST['invitation_content'];
	mail_invitation($emails, $content, $name);
	Session::Set('notice', '邀请发送成功');
	redirect( WEB_ROOT . '/account/invite.php' );
}


$condition = array( 
		'user_id' => $login_user_id, 
		'credit > 0',
		'pay' => 'Y',
		);
$money = Table::Count('invite', $condition, 'credit');
$count = Table::Count('invite', $condition);

$team = current_team($city['id']);
die(include template('account_invite'));
