<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
$user = Table::Fetch('user',$login_user_id);
$mail = uencode($user['email']);
$condition = array( 
		'user_id' => $login_user_id, 
		'pay' => 'Y',
		);
$count = Table::Count('invite', $condition);
$money = Table::Count('invite', $condition, 'credit');

list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$invites = DB::LimitQuery('invite', array(
			'condition' => $condition,
			'order' => 'ORDER BY buy_time DESC',
			'size' => $pagesize,
			'offset' => $offset,
			));

$user_ids = Utility::GetColumn($invites, 'other_user_id');
$team_ids = Utility::GetColumn($invites, 'team_id');

$users = Table::Fetch('user', $user_ids);
$teams = Table::Fetch('team', $team_ids);

$pagetitle = '我的邀请';
include template('account_referdone');
