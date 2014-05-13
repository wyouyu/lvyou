<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_partner();
$id = abs(intval($_GET['id']));

$partner_id = abs(intval($_SESSION['partner_id']));
$login_partner = Table::Fetch('partner', $partner_id);

$team = Table::Fetch('team', $id);
if($team['partner_id'] != $partner_id) {
	Session::Set('error', '无权访问数据');
	redirect( WEB_ROOT  . '/biz/index.php');
}

$condition = array(
		'state' => 'pay',
		'team_id' => $id,
		);
$orders = DB::LimitQuery('order', array(
			'condition' => $condition,
			'order' => 'ORDER BY id DESC',
			));

if (!$orders) die('-ERR ERR_NO_DATA');
$users = Table::Fetch('user', Utility::GetColumn($orders, 'user_id'));
if ($team['delivery'] == 'coupon') {
	$name = 'coupon_'.date('Ymd');
	$condition = array( 'team_id' => $id,);
	$coupons = DB::LimitQuery('coupon', array( 'condition' => $condition,));
	$kn = array(
			'id' => '编号',
			'username' => '用户名',
			'secret' => '密码',
			'condbuy' => '选项',
			'date' => '到期日',
			'consume' => '状态',
			);
	if (!$INI['system']['partnerdown']) unset($kn['secret']);

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
		$one['date'] = date('Y-m-d', $one['expire_time']);
		$ecoupons[] = $one;
	}
	down_xls($ecoupons, $kn, $name);
}

//delivery
$name = 'order_'.date('Ymd');
$kn = array(
		'id' => '订单号',
		'pay_id' => '支付号',
		'service' => '支付方式',
		'price' => '单价',
		'quantity' => '数量',
		'fare' => '运费',
		'origin' => '总金额',
		'money' => '支付款',
		'credit' => '余额付款',
		'state' => '支付状态',
		'condbuy' => '选项',
		'remark' => '备注',
		'express' => '快递信息',
		'username' => '用户名',
		'useremail' => '用户邮箱',
		'usermobile' => '用户手机',
		'realname' => '收件人',
		'mobile' => '手机号码',
		'zipcode' => '邮政编码',
		'address' => '送货地址',
		);

if (option_yes('userprivacy')) {
	unset($kn['username']);
	unset($kn['useremail']);
	unset($kn['usermobile']);
	unset($kn['money']);
	unset($kn['credit']);
}

$pay = array(
		'alipay' => '支付宝',
		'tenpay' => '财付通',
		'chinabank' => '网银在线',
		'paypal' => 'Pyapal',
		'yeepay' => '易宝',
		'chinabank' => '网银在线',
		'credit' => '余额付款',
		'cash' => '现金支付',
		'' => '其他',
		);

$state = array(
		'unpay' => '未支付',
		'pay' => '已支付',
		);
$eorders = array();

$expresses = option_category('express');
foreach( $orders AS $one ) {
	$oneuser = $users[$one['user_id']];
	$one['username'] = $oneuser['username'];
	$one['useremail'] = $oneuser['email'];
	$one['usermobile'] = $oneuser['mobile'] ? $oneuser['mobile'] : $one['mobile'];
	$one['fare'] = ($one['delivery'] == 'express') ? $one['fare'] : 0;
	$one['service'] = $pay[$one['service']];
	$one['price'] = $team['market_price'];
	$one['state'] = $state[$one['state']];
	$one['express'] = ($one['express_id'] && $one['express_no']) ? 
		$expresses[$one['express_id']] . ":" . $one['express_no']
		: "";
	$eorders[] = $one;
}
down_xls($eorders, $kn, $name);
