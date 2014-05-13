<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin|market');

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));
$team = Table::Fetch('team', $id);

if ( 'removeteamvoucher' == $action ) {
	DB::Delete('voucher', array(
		'team_id' => $id,
		'order_id' => 0,
	));
	$cond = array( 'team_id' => $id );
	$count = Table::Count('voucher', $cond);
	Table::UpdateCache('team', $id, array('max_number'=> $count));
	Session::Set('notice', '未下发的商户券清空完成');
	json(null, 'refresh');
}
if ( 'removeonevoucher' == $action ) {
	$vid = strval($_GET['vid']);
	$voucher = Table::Fetch('voucher', $vid);
	if ($voucher['order_id']) {
		json('商户券已分配，不可删除', 'alert');
	} else if (!$voucher ) {
		json('商户券不存在', 'alert');
	}
	Table::Delete('voucher', $vid);
	$cond = array( 'team_id' => $voucher['team_id'] );
	$count = Table::Count('voucher', $cond);
	Table::UpdateCache('team', $voucher['team_id'],
			array( 'max_number' => $count ));
	Session::Set('notice', '商户券删除成功');
	json(null, 'refresh');
}
elseif ( 'smsexpress' == $action ) {
	$con_pay = array(
			'team_id' => $id,
			'state' => 'pay',
			);
	$con_smsneed = array(
			'team_id' => $id,
			'state' => 'pay',
			'express_id > 0',
			'sms_express' => 'N',
			);
	$con_smsyes = array(
			'team_id' => $id,
			'state' => 'pay',
			'sms_express' => 'Y',
			);

	$count_pay = Table::Count('order',$con_pay);
	$count_smsneed = Table::Count('order',$con_smsneed);
	$count_smsyes = Table::Count('order',$con_smsyes);

	$s = "总付款订单：{$count_pay}，已发短信：{$count_smsyes}，待发短信：{$count_smsneed}";

	$n = time();
	$orders = DB::LimitQuery('order', array(
				'condition' => $con_smsneed,
				));

	$index = 0;
	foreach($orders AS $one) {
		$r = sms_express($one['id']);
		$q = time();
		$index++;
		if ($q-$n>20) json("{$s}，本次发送：{$index}", 'alert');
	}
	json("{$s}，本次发送：{$index}", 'alert');
}
