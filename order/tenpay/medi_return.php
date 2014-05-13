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
	$trade_no = $resHandler->getParameter("cft_tid");
	//金额,以分为单位
	$v_amount = moneyit($resHandler->getParameter("total_fee")*0.01);
	//返回码
	$retcode = $resHandler->getParameter("retcode");
	//状态
	$status = $resHandler->getParameter("status");	
	list($_, $order_id, $city_id, $_) = explode('-', $v_oid, 4);
	//返回码判断
	if( "0" == $retcode ) {

		switch ($status) {
			case "3":	//买家付款成功，注意判断订单是否重复的逻辑
			case "5":	//买家收货确认，交易成功
				$currency = 'CNY';
				$service = 'tenpay';
				$bank = '财付通';
				ZOrder::OnlineIt($order_id, $v_oid, $v_amount, $currency, $service, $bank,$trade_no);
				break;
			default:
				//nothing to do
				break;
		}
		
	} else {
		echo "支付失败";
	}
	//调用doShow
	$resHandler->doShow();	
} else {
	echo "<br/>" . "认证签名失败" . "<br/>";
}
?>