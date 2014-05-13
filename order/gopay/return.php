<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

$tranCode = $_REQUEST["tranCode"];
$merchantID = $_REQUEST["merchantID"];
$merOrderNum = $_REQUEST["merOrderNum"];
$tranAmt = $_REQUEST["tranAmt"];
$ticketAmt = $_REQUEST["ticketAmt"];
$tranDateTime = $_REQUEST["tranDateTime"];
$currencyType = $_REQUEST["currencyType"];
$merURL = $_REQUEST["merURL"];
$customerEMail = $_REQUEST["customerEMail"];
$authID = $_REQUEST["authID"];
$orgOrderNum = $_REQUEST["orgOrderNum"];
$orgtranDateTime = $_REQUEST["orgtranDateTime"];
$orgtranAmt = $_REQUEST["orgtranAmt"];
$orgTxnType = $_REQUEST["orgTxnType"];
$orgTxnStat = $_REQUEST["orgTxnStat"];
$msgExt = $_REQUEST["msgExt"];
$virCardNo = $_REQUEST["virCardNo"];
$virCardNoIn = $_REQUEST["virCardNoIn"];
$tranIP = $_REQUEST["tranIP"];
$isLocked = $_REQUEST["isLocked"];
$feeAmt = $_REQUEST["feeAmt"];
$respCode = $_REQUEST["respCode"];
$VerficationCode = $INI['gopay']['code'];
$signValue = $_REQUEST["signValue"];
$orderId = preg_replace('/_/', '-', $merOrderNum);
$str = "tranCode=[$tranCode]merchantID=[$merchantID]merOrderNum=[$merOrderNum]tranAmt=[$tranAmt]ticketAmt=[$ticketAmt]tranDateTime=[$tranDateTime]currencyType=[$currencyType]merURL=[$merURL]customerEMail=[$customerEMail]authID=[$authID]orgOrderNum=[$orgOrderNum]orgtranDateTime=[$orgtranDateTime]orgtranAmt=[$orgtranAmt]orgTxnType=[$orgTxnType]orgTxnStat=[$orgTxnStat]msgExt=[$msgExt]virCardNo=[$virCardNo]virCardNoIn=[$virCardNoIn]tranIP=[$tranIP]isLocked=[$isLocked]feeAmt=[$feeAmt]respCode=[$respCode]VerficationCode=[$VerficationCode]";
$newSign = MD5($str);


@list($_, $order_id, $city_id, $_) = explode('-', $orderId, 4);
if (Table::Fetch('pay', $orderId)) die('SUCCESS');

	if( $newSign == $signValue && $respCode == '0000' ) {
		/* charge */
		if ( $_ == 'charge' ) {
			@list($_, $user_id, $create_time, $_) = explode('-', $orderId, 4);
		    ZFlow::CreateFromCharge($tranAmt, $user_id, $create_time, 'gopay',$tranCode);
			Session::Set('notice', "国付宝充值{$tranAmt}元成功！");
			redirect(WEB_ROOT . '/credit/index.php');
			die('success');
		}
		/* end charge */
		$currency = 'CNY';
		$service = 'gopay';
		$bank = '国付宝';
		ZOrder::OnlineIt($order_id, $orderId, $tranAmt, $currency, $service, $bank,$tranCode);
		//Session::Set('notice', "购买成功！");
		redirect(WEB_ROOT . "/order/pay.php?id={$order_id}" );
		die('success');
	} 
 
include template('order_return_error');
?>
