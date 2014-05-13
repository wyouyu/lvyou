<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin|market');

$id = abs(intval($_GET['id']));
$team = Table::Fetch('team', $id);

$o_con = array(
		'state' => 'pay',
		'team_id' => $id,
		);
$orders = DB::LimitQuery('order', array(
			'condition' => $o_con,
			));
$user_ids = Utility::GetColumn($orders, 'user_id');
$users = Table::Fetch('user', $user_ids);

$kn = array(
		'username' => '用户名',
		'umobile' => '用户手机',
		'mobile' => '订单手机',
		'buy_id' => '付款顺序',
		'luky_id' => '幸运号',
		);

foreach($orders As $k=>$o) {
	$ouser = $users[$o['user_id']];
	$o['username'] = $ouser['username'];
	$o['umobile'] = $ouser['umobile'];
	$orders[$k] = $o;
}

$name = "luck_{$id}_".date('Ymd');
down_xls($orders, $kn, $name);
