<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();

$id = strval($_GET['id']);
$coupon = Table::Fetch('coupon', $id);

if (!$coupon) {
	Session::Set('error', "{$INI['system']['couponname']}不存在");
	redirect(WEB_ROOT . '/coupon/index.php');
}

if ($coupon['user_id'] != $login_user_id) { 
	Session::Set('error', "本单{$INI['system']['couponname']}不属于你");
	redirect(WEB_ROOT . '/coupon/index.php');
}

$partner = Table::Fetch('partner', $coupon['partner_id']);
$team = Table::Fetch('team', $coupon['team_id']);

$pagetitle = '打印优惠券';
include template('coupon_print');
