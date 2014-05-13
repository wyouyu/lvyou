<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$actions = array('store', 'charge','cardstore','paycharge', 'withdraw', 'cash', 'refund');

($s = strtolower(strval($_GET['s']))) || ($s = 'store');
if(!$s||!in_array($s, $actions)) $s = 'store';

if ('store' == $s ) {
	$condition = array( 'action' => 'store', );
	$count = Table::Count('flow', $condition);
	$summary = Table::Count('flow', $condition, 'money');
	list($pagesize, $offset, $pagestring) = pagestring($count, 20);
	$flows = DB::LimitQuery('flow', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'offset' => $offset,
		'size' => $pagesize,
	));
	$user_ids = Utility::GetColumn($flows, 'user_id');
	$admin_ids = Utility::GetColumn($flows, 'admin_id');
	$users = Table::Fetch('user', array_merge($user_ids,$admin_ids));
	include template('manage_misc_money_store');
}
elseif ('charge' == $s ) {
	$condition = array( 'action' => 'charge', );
	$count = Table::Count('flow', $condition);
	$summary = Table::Count('flow', $condition, 'money');
	list($pagesize, $offset, $pagestring) = pagestring($count, 20);
	$flows = DB::LimitQuery('flow', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'offset' => $offset,
		'size' => $pagesize,
	));
	$user_ids = Utility::GetColumn($flows, 'user_id');
	$admin_ids = Utility::GetColumn($flows, 'admin_id');
	$users = Table::Fetch('user', array_merge($user_ids,$admin_ids));
	$pay_ids = Utility::GetColumn($flows, 'detail_id');
	$pays = Table::Fetch('pay', $pay_ids);
	include template('manage_misc_money_charge');
}
elseif ('cardstore' == $s ) {
	$condition = array( 'action' => 'cardstore', );
	$count = Table::Count('flow', $condition);
	$summary = Table::Count('flow', $condition, 'money');
	list($pagesize, $offset, $pagestring) = pagestring($count, 20);
	$flows = DB::LimitQuery('flow', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'offset' => $offset,
		'size' => $pagesize,
	));
	$user_ids = Utility::GetColumn($flows, 'user_id');
	$admin_ids = Utility::GetColumn($flows, 'admin_id');
	$users = Table::Fetch('user', array_merge($user_ids,$admin_ids));
	$pay_ids = Utility::GetColumn($flows, 'detail_id');
	$pays = Table::Fetch('pay', $pay_ids);
	include template('manage_misc_money_chargebycard');
}
elseif ('paycharge' == $s ) {
	$condition = array( 'action' => 'paycharge', );
	$count = Table::Count('flow', $condition);
	$summary = Table::Count('flow', $condition, 'money');
	list($pagesize, $offset, $pagestring) = pagestring($count, 20);
	$flows = DB::LimitQuery('flow', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'offset' => $offset,
		'size' => $pagesize,
	));
	$user_ids = Utility::GetColumn($flows, 'user_id');
	$admin_ids = Utility::GetColumn($flows, 'admin_id');
	$users = Table::Fetch('user', array_merge($user_ids,$admin_ids));
	$pay_ids = Utility::GetColumn($flows, 'detail_id');
	$pays = Table::Fetch('pay', $pay_ids);
	include template('manage_misc_money_chargebypay');
}
else if ('withdraw' == $s ) {
	$condition = array( 'action' => 'withdraw', );
	$count = Table::Count('flow', $condition);
	$summary = Table::Count('flow', $condition, 'money');
	list($pagesize, $offset, $pagestring) = pagestring($count, 20);
	$flows = DB::LimitQuery('flow', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'offset' => $offset,
		'size' => $pagesize,
	));
	$user_ids = Utility::GetColumn($flows, 'user_id');
	$admin_ids = Utility::GetColumn($flows, 'admin_id');
	$users = Table::Fetch('user', array_merge($user_ids,$admin_ids));
	include template('manage_misc_money_store');
}
else if ( 'cash' == $s ) {
	$condition = array( 'service' => 'cash', 'state' => 'pay', );
	$summary = Table::Count('order', $condition, 'money');
	$count = Table::Count('order', $condition);
	list($pagesize, $offset, $pagestring) = pagestring($count, 20);
	$orders = DB::LimitQuery('order', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'offset' => $offset,
		'size' => $pagesize,
	));

	$user_ids = Utility::GetColumn($orders, 'user_id');
	$admin_ids = Utility::GetColumn($orders, 'admin_id');
	$users = Table::Fetch('user', array_merge($user_ids,$admin_ids));

	$team_ids = Utility::GetColumn($orders, 'team_id');
	$teams = Table::Fetch('team', $team_ids);
	include template('manage_misc_money_cash');
}
else if ( 'refund' == $s ) {
	$condition = array( 'action' => 'refund', );
	if($_POST['id']) $condition[] = " user_id = {$_POST['id']}";
	if($_POST['username']) {
		$uid = Table::Fetch('user',$_POST['username'],'username');
		$condition[] = " user_id = {$uid['id']}";
	}
	$summary = Table::Count('flow', $condition, 'money');
	$count = Table::Count('flow', $condition);
	list($pagesize, $offset, $pagestring) = pagestring($count, 20);
	$flows = DB::LimitQuery('flow', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'offset' => $offset,
		'size' => $pagesize,
	));

	$user_ids = Utility::GetColumn($flows, 'user_id');
	$admin_ids = Utility::GetColumn($flows, 'admin_id');
	$users = Table::Fetch('user', array_merge($user_ids,$admin_ids));

	$team_ids = Utility::GetColumn($flows, 'detail_id');
	$teams = Table::Fetch('team', $team_ids);
	include template('manage_misc_money_refund');
}
else redirect( WEB_ROOT . '/manage/misc/money.php' );
