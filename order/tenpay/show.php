<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

/* 平台商密钥 */
$key = $INI['tenpay']['sec'];

/* 创建支付应答对象 */
$resHandler = new MediPayResponseHandler();
$resHandler->setKey($key);

//判断签名
if($resHandler->isTenpaySign()) {
	//商家订单号
	$v_oid = $resHandler->getParameter("mch_vno");
	@list($_, $order_id, $city_id, $_) = explode('-', $v_oid, 4);
	//金额,以分为单位
	$v_amount = moneyit($resHandler->getParameter("total_fee")*0.01);
	//返回码
	$retcode = $resHandler->getParameter("retcode");
	//状态
	$status = $resHandler->getParameter("status");	
	//返回码判断
	if( "0" == $retcode ) {
		$order = Table::Fetch('order', $order_id);
		if ( $order['state'] == 'pay' ) {
		Session::Set('notice', "购买成功！");
		}
	} else {
		Session::Set('notice', "支付失败！");
	}	
} 

redirect( WEB_ROOT . "/order/pay.php?id={$order_id}");

?>