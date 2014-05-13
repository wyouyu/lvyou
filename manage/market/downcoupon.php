<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

if ( $_POST ) {
	$team_id = abs(intval($_POST['team_id']));
	$consume = $_POST['consume'];
	if (!$team_id || !$consume) die('-ERR ERR_NO_DATA');
	
	$condition = array(
		'team_id' => $team_id,
		'consume' => $consume,
	);

	$coupons = DB::LimitQuery('coupon', array(
		'condition' => $condition,
	));
	if (!$coupons) die('-ERR ERR_NO_DATA');

	$users = Table::Fetch('user',Utility::GetColumn($coupons,'user_id'));
	$orders = Table::Fetch('order',Utility::GetColumn($coupons,'order_id'));

	$team = Table::Fetch('team', $team_id);
	$name = 'coupon_'.date('Ymd');
	$kn = array(
		'id' => '编号',
		'buy_id' => '支付序号',
		'username' => '用户名',
		'secret' => '密码',
		'condbuy' => '选项',
		'date' => '到期日',
		'remark' => '备注',
		'consume' => '状态',
		);

	$consume = array(
		'Y' => '已消费',
		'N' => '未消费',
	);
	$ecoupons = array();
	foreach( $coupons AS $one ) {
		$one['id'] = "#{$one['id']}";
		$one['username'] = $users[$one['user_id']]['username'];
		$one['consume'] = $consume[$one['consume']];
		$one['condbuy'] = $orders[$one['order_id']]['condbuy'];
		$one['remark'] = $orders[$one['order_id']]['remark'];
		$one['date'] = date('Y-m-d', $one['expire_time']);
		$ecoupons[] = $one;
	}
	down_xls($ecoupons, $kn, $name);
}

include template('manage_market_downcoupon');
