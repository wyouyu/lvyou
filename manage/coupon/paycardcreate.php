<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('coupon');

if (is_post()){
	$paycard = $_POST;

	$paycard['quantity'] = abs(intval($paycard['quantity']));
	$paycard['money'] = abs(intval($paycard['money']));
	$paycard['expire_time'] = strtotime($paycard['expire_time']);

	$error = array();
	if ( $paycard['money'] < 10 ) {
		$error[] = "充值卡面额不能小于10元";
	}
	if ( $paycard['quantity'] < 1 || $card['quantity'] > 1000 ) {
		$error[] = "充值卡每次只能生产1-1000张";
	}
	$today = strtotime(date('Y-m-d'));
	if ( $paycard['expire_time'] < $today ) {
		$error[] = "过期时间不能小于当天";
	}
	if ( !$error && ZPaycard::PayCardCreate($paycard) ) {
        log_admin('coupon', '新建充值卡'.$paycard['quantity'].'张');
		Session::Set('notice', "{$paycard['quantity']}张充值卡生成成功");
		redirect(WEB_ROOT . '/manage/coupon/paycardcreate.php');
	}
	$error = join("<br />", $error);
	Session::Set('error', $error);
}
else {
	$paycard = array(
		'expire_time' => strtotime('+6 months'),
		'quantity' => 10,
		'money' => 50,
	);
}

include template('manage_coupon_paycardcreate');
