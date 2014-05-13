<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('coupon');

if (is_post()){
	$card = $_POST;

	$card['quantity'] = abs(intval($card['quantity']));
	$card['money'] = abs(intval($card['money']));
	$card['partner_id'] = abs(intval($card['partner_id']));
	$card['begin_time'] = strtotime($card['begin_time']);
	$card['end_time'] = strtotime($card['end_time']);

	$error = array();
	if ( $card['money'] < 1 ) {
		$error[] = "代金券面额不能小于1元";
	}
	if ( $card['quantity'] < 1 || $card['quantity'] > 1000 ) {
		$error[] = "代金券每次只能生产1-1000枚";
	}
	$today = strtotime(date('Y-m-d'));
	if ( $card['begin_time'] < $today ) {
		$error[] = "开始时间不能小于当天";
	}
	elseif ( $card['end_time'] < $card['begin_time'] ) {
		$error[] = "结束时间不能小于开始时间";
	}
	if ( !$error && ZCard::CardCreate($card) ) {
        log_admin('coupon', '新建代金券'.$card['quantity'].'张');
		Session::Set('notice', "{$card['quantity']}张代金券生成成功");
		redirect(WEB_ROOT . '/manage/coupon/cardcreate.php');
	}
	$error = join("<br />", $error);
	Session::Set('error', $error);
}
else {
	$card = array(
		'begin_time' => time(),
		'end_time' => strtotime('+3 months'),
		'quantity' => 10,
		'money' => 10,
		'code' => date('Ymd').'_ZT',
	);
}

include template('manage_coupon_cardcreate');
