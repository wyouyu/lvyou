<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

$key = $INI['tenpay']['sec'];

$resHandler = new PayResponseHandler();
$resHandler->setKey($key);
if($resHandler->isTenpaySign()) {
	$v_oid = $resHandler->getParameter("sp_billno");
	$trade_no = $resHandler->getParameter("transaction_id");
	$v_amount = moneyit($resHandler->getParameter("total_fee")*0.01);
	$pay_result = $resHandler->getParameter("pay_result");
	list($_, $order_id, $city_id, $_) = explode('-', $v_oid, 4);

	if( "0" == $pay_result ) {

		/* charge */
		if ( $_ == 'charge' ) {
			@list($_, $user_id, $create_time, $_) = explode('-', $v_oid, 4);
			if(ZFlow::CreateFromCharge($v_amount, $user_id, $create_time, 'tenpay',$trade_no)){
				Session::Set('notice', "财付通充值{$v_amount}元成功！");
			}
			redirect(WEB_ROOT . '/credit/index.php');
		}
		/* end charge */

		$currency = 'CNY';
		$service = 'tenpay';
		$bank = '财付通';
		ZOrder::OnlineIt($order_id, $v_oid, $v_amount, $currency, $service, $bank,$trade_no);
		$show = WEB_ROOT . "/order/gopay.php?id={$order_id}";
		
		$resHandler->doShow($show);
		die(0);
	} 
}
include template('order_return_error');
?>
