<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$action = strval($_GET['action']);
$secret = strval($_GET['secret']);


if ($action == 'dialog') {
	$html = render('ajax_dialog_chargecard');
	json($html, 'dialog');
}
else if($action == 'query') {
	$paycard = Table::FetchForce('paycard', $secret);
	$e = date('Y-m-d', $paycard['expire_time']);
	if (!$paycard) { 
		$v[] = "{$secret}&nbsp;密码无效";
	} else if ( $paycard['consume'] == 'Y' ) {
		$v[] = '本卡已充值';
		$v[] = '充值于：' . date('Y-m-d H:i:s', $paycard['recharge_time']);
	} else if ( $paycard['expire_time'] < strtotime(date('Y-m-d')) ) {
		$v[] = "{$secret}&nbsp;本卡已过期";
		$v[] = '过期日期：' . date('Y-m-d', $paycard['expire_time']);
	} else {
		$v[] = "{$secret}&nbsp;该卡有效";
		$v[] = "金额:{$paycard['value']}";
		$v[] = "有效期至&nbsp;{$e}";
	}
	$v = join('<br/>', $v);
	$d = array(
			'html' => $v,
			'id' => 'coupon-dialog-display-id',
			);
	json($d, 'updater');
}

else if($action == 'consume') {
	global $login_user_id;
        $paycard = Table::FetchForce('paycard', $secret);
	if (!$login_user_id) {
		$v[] = "登陆后才能进行充值";
		$v[] = '本次充值失败';
	}else if (!$paycard) {
		$v[] = "{$secret}&nbsp;充值卡无效";
		$v[] = '本次充值失败';
	}else if ($paycard['id']!=$secret) {
		$v[] = '充值密码不正确';
		$v[] = '本次充值失败';
	} else if ( $paycard['expire_time'] < strtotime(date('Y-m-d')) ) {
		$v[] = "{$secret}&nbsp;充值卡已过期";
		$v[] = '过期时间：' . date('Y-m-d', $paycard['expire_time']);
		$v[] = '本次充值失败';
	} else if ( $paycard['consume'] == 'Y' ) {
		$v[] = "{$secret}&nbsp;充值卡已充值";
		$v[] = '充值于：' . date('Y-m-d H:i:s', $paycard['recharge_time']);
		$v[] = '本次充值失败';
	} else {
		ZPaycard::UsePayCard($paycard);
        $v[] = '充值成功' ;
        $v[] = "充值金额:{$paycard['value']}" ;
		$v[] = '充值时间：' . date('Y-m-d H:i:s', time());	
	}
	$v = join('<br/>', $v);
	$d = array(
			'html' => $v,
			'id' => 'coupon-dialog-display-id',
			);
	json($d, 'updater');
}
