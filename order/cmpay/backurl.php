<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

require("globalparam.php"); 
require("globalfunction.php"); 

//报文头
$hmac = $_REQUEST["hmac"];
$merchantId = $_REQUEST["merchantId"];
$payNo = $_REQUEST["payNo"];
$requestId = $_REQUEST["requestId"];
$returnCode = $_REQUEST["returnCode"];
$message = $_REQUEST["message"];
$message = decodeUtf8($message);
$sigTyp = $_REQUEST["signType"];
$type = $_REQUEST["type"];
$version = $_REQUEST["version"];
	    
//报文体
$amount = $_REQUEST["amount"];        
$banks = $_REQUEST["banks"];        
$contractName = $_REQUEST["contractName"];  
$contractName = decodeUtf8($contractName);            
$invoiceTitle = $_REQUEST["invoiceTitle"];   
$invoiceTitle = decodeUtf8($invoiceTitle);     
$mobile = $_REQUEST["mobile"];
$orderId = $_REQUEST["orderId"];        
$payDate = $_REQUEST["payDate"];        
$reserved = $_REQUEST["reserved"];   
$reserved = decodeUtf8($reserved);     
$status = $_REQUEST["status"];  
$amtItem = $_REQUEST["amtItem"];      
$signData = $merchantId.$payNo.$requestId.$returnCode.$message.$sigTyp.$type.$version	.$amount.$banks.$contractName.$invoiceTitle.$mobile.$orderId.$payDate.$reserved.$status;
//if($version == "1.0.1")
$signData = $merchantId.$payNo.$requestId.$returnCode.$message.$sigTyp.$type.$version	.$amount.$banks.$contractName.$invoiceTitle.$mobile.$orderId.$payDate.$reserved.$status.$amtItem;
$hash = hmac("",$signData);
$newhmac = hmac($signKey,$hash);

RecordLog("YGM","###backurl".$hmac."backurl###");
RecordMyLog("*backurl*".$hmac."*backurl*");

@list($_, $order_id, $city_id, $_) = explode('-', $orderId, 4);
$v_amount = $amount/100;
//print_r($v_amount);
//exit;
if ( $_ == 'charge' ) {
	if($newhmac == $hmac)
                $pay = Table::Fetch('pay', $orderId);
        if ( $pay ) {
		Session::Set('notice', "手机支付充值{$v_amount}元成功！");
		}  
        /* @list($_, $user_id, $create_time, $_) = explode('-', $orderId, 4);
		if(ZFlow::CreateFromCharge($v_amount, $user_id, $create_time, 'cmpay')){
			Session::Set('notice', "手机支付充值{$v_amount}元成功！");
		} 
        */
		redirect(WEB_ROOT . '/credit/index.php');
}


if($newhmac == $hmac) {
	  $order = Table::Fetch('order', $order_id);
	  if ( $order['state'] == 'pay' ) {
		Session::Set('notice', "购买成功！");
	 }
     redirect( WEB_ROOT . "/order/pay.php?id={$order_id}");
}
else{
     // echo("验签失败！");
     include template('order_return_error');
}   







  
?>


