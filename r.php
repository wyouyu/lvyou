<?php
require_once(dirname(__FILE__) . '/app.php');

$mail = strval($_GET['r']);
if ($mail) {
	$mail = udecode($mail);
	$user = Table::Fetch('user',$mail,'email');
	$id = $user['id'];	
	if ($login_user_id) {
		ZInvite::CreateFromId($id, $login_user_id);
	} else {
		$longtime = 86400 * 3; //3 days
		cookieset('_rid', $id, $longtime);
	}
}
redirect( WEB_ROOT  . '/index.php');
