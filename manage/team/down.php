<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

$id = abs(intval($_GET['id']));
$team = Table::Fetch('team', $id);

if ( $team['delivery']=='express' ) {
	$oc = array(
		'state' => 'pay',
		'team_id' => $id,
	);
	$orders = DB::LimitQuery('order', array(
		'condition' => $oc,
		'order' => 'ORDER BY pay_time DESC, id DESC',
	));
	$kn = array(
		'buy_id' => '支付序号',
		'username' => '用户名',
		'email' => '用户邮箱',
		'realname' => '姓名',
		'mobile' => '手机号码',
		'address' => '地址',
		'quantity' => '数量',
		'condbuy' => '选项',
		'remark' => '备注',
		'date' => '支付时间',
	);

	foreach($orders As $k=>$o) {
		$o['date'] = date('Y-m-d H:i', $o['pay_time']);
		$orders[$k] = $o;
	}

	$name = "team_{$id}_".date('Ymd');
	down_xls($orders, $kn, $name);
}
else {
	$cc = array(
		'team_id' => $id,
		);
	$coupons = DB::LimitQuery('coupon', array(
				'condition' => $cc,
				'order' => 'ORDER BY create_time ASC',
				));
	$users = Table::Fetch('user', Utility::GetColumn($coupons, 'user_id'));
	$orders = Table::Fetch('order', Utility::GetColumn($coupons, 'order_id'));
	$kn = array(
			'buy_id' => '支付序号',
			'username' => '用户名',
			'email' => '用户邮箱',
			'realname' => '姓名',
			'mobile' => '手机号码',
			'condbuy' => '选项',
			'id' => "{$INI['system']['couponname']}编号",
			'secret' => "{$INI['system']['couponname']}密码",
			'cmobile' => '消费手机',
			'date' => '生成时间',
			'remark' => '备注',
	);
	
	foreach($coupons As $k=>$o) {
		$u = $users[$o['user_id']];
		$r = $orders[$o['order_id']];

		$o['buy_id'] = $r['buy_id'];
		$o['username'] = $u['username'];
		$o['realname'] = $u['realname'];
		$o['condbuy'] = $r['condbuy'];
		$o['mobile'] = $u['mobile'];
		$o['email'] = $u['email'];
		$o['cmobile'] = $r['mobile'] ? $r['mobile'] : $u['mobile'];
		$o['date'] = date('Y-m-d H:i', $o['create_time']);
		$o['remark'] = $r['remark'];
		$coupons[$k] = $o;
	}
	$name = "team_{$id}_".date('Ymd');
	down_xls($coupons, $kn, $name);
}
