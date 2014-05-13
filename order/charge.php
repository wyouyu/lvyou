<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');

need_login();
$total_money = abs(floatval($_POST['money']));
$action = strval($_POST['action']);
if (!$total_money && $action != 'redirect') {
	Session::Set('error', '充值金额至少1元');
	redirect( WEB_ROOT . '/credit/charge.php' );
}

$order_service = pay_getservice($_POST['paytype']);
$title = "{$login_user['email']}({$INI['system']['sitename']}充值{$total_money}元)";

$now = time();
$randid = strtolower(Utility::GenSecret(4, Utility::CHAR_WORD));
$charge_id = "charge-{$login_user_id}-{$now}-{$randid}";

/* credit pay */
if ( $_POST['action'] == 'redirect' ) {
	redirect($_POST['reqUrl']);
}

$pay_callback = "pay_charge_{$order_service}";
if ( function_exists($pay_callback) ) {
	$payhtml = $pay_callback($total_money, $charge_id, $title);
	die(include template('order_charge'));
}
else {
	redirect( WEB_ROOT. "/credit/index.php");
}
