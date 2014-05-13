<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
$user = Table::Fetch('user',$login_user_id);
$mail = uencode($user['email']);
$etime = strtotime('7 days ago');
$condition = array( 
	'user_id' => $login_user_id, 
	'team_id' => 0,
	"create_time > {$etime}",
);
$count = Table::Count('invite', $condition);

list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$invites = DB::LimitQuery('invite', array(
			'condition' => $condition,
			'order' => 'ORDER BY buy_time DESC',
			'size' => $pagesize,
			'offset' => $offset,
			));

$user_ids = Utility::GetColumn($invites, 'other_user_id');
$users = Table::Fetch('user', $user_ids);

$pagetitle = '我的邀请';
include template('account_referpending');
