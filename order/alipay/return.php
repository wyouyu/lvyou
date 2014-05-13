<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

$_input_charset = 'utf-8';
$partner = $INI['alipay']['mid'];
$security_code = $INI['alipay']['sec'];
$sign_type = 'MD5';
$transport = 'http';

/* very import, this value is add by my phpframewrok */
unset($_GET['param']);  
/* end */

$alipay = new AlipayNotify($partner, $security_code, $sign_type, $_input_charset, $transport);
$verify_result = $alipay->return_verify();

$out_trade_no = $_GET['out_trade_no'];   //获取订单号
$total_fee  = $_GET['total_fee'];      //获取总价格  
@list($_, $order_id, $city_id, $_) = explode('-', $out_trade_no, 4);
/*exit($out_trade_no);
if (Table::Fetch('pay', $out_trade_no)) {
	redirect( WEB_ROOT . "/order/pay.php?id={$order_id}");
}*/

if ( $_ == 'charge' ) {
	if ( $verify_result ) {
		$pay = Table::Fetch('pay', $out_trade_no);
		if ( $pay ) {
			Session::Set('notice', "支付宝充值{$total_fee}元成功！");
		}
	}
	redirect(WEB_ROOT . '/credit/index.php');
}

if($verify_result) {
	//exit('123');
	$order = Table::Fetch('order', $order_id);
	if ( $order['state'] == 'pay' ) {
		redirect(WEB_ROOT . "/order/gopay.php?id={$order_id}");
	}
}

redirect( WEB_ROOT . "/order/pay.php?id={$order_id}");
