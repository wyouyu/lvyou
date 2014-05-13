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
      
// if($version == "1.0.1")
$signData = $merchantId.$payNo.$requestId.$returnCode.$message.$sigTyp.$type.$version .$amount.$banks.$contractName.$invoiceTitle.$mobile.$orderId.$payDate.$reserved.$status.$amtItem;
$hash = hmac("",$signData);
$newhmac = hmac($signKey,$hash);
			
RecordLog("YGM","###hmac".$hmac."###");
RecordLog("YGM","###newhmac".$newhmac."###");


@list($_, $order_id, $city_id, $_) = explode('-', $orderId, 4);
if (Table::Fetch('pay', $orderId)) die('SUCCESS');
$v_amount = $amount/100;
if ( $_ == 'charge' ) {
   if($newhmac == $hmac){
		@list($_, $user_id, $create_time, $_) = explode('-', $orderId, 4);
		ZFlow::CreateFromCharge($v_amount, $user_id, $create_time, 'cmpay');
        
                // 记录日志
                
                RecordMyLog("流水号:".$payNo);
                RecordMyLog("支付金额:".$amount);
         	RecordMyLog("金额明细:".$amtItem);
		RecordMyLog("支付银行:".$banks);
		RecordMyLog("送货信息：".$contractName);
		RecordMyLog("发票抬头：".$invoiceTitle);
		RecordMyLog("支付人：".$mobile);
		RecordMyLog("支付时间：".$payDate);
		RecordMyLog("保留字段：".$reserved);
		RecordMyLog("支付结果：".$status);
                
	}
        die("SUCCESS");  
}

if ($newhmac == $hmac) {
		$currency = 'CNY';
		$service = 'cmpay';
		$bank = '手机支付';
		ZOrder::OnlineIt($order_id, $orderId, $v_amount, $currency, $service, $bank);

                // 记录日志
                
                RecordMyLog("流水号:".$payNo);
                RecordMyLog("支付金额:".$amount);
         	RecordMyLog("金额明细:".$amtItem);
		RecordMyLog("支付银行:".$banks);
		RecordMyLog("送货信息：".$contractName);
		RecordMyLog("发票抬头：".$invoiceTitle);
		RecordMyLog("支付人：".$mobile);
		RecordMyLog("支付时间：".$payDate);
		RecordMyLog("保留字段：".$reserved);
		RecordMyLog("支付结果：".$status);
                
                die('SUCCESS');
} 
else{
                RecordMyLog("验签失败！");
                die('验签失败！');
       } 
?>
