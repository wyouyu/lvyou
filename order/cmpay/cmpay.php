<?php

require_once('globalparam.php');
require_once('globalfunction.php');

 
//print_r($_POST);
//exit;

$url = $GLOBALS['tokenReqUrl'];
//报头数据
$callbackUrl = $GLOBALS['callbackUrl'];
$hmac = "";
$ipAddress = Utility::GetRemoteIp();;
$merchantId = $GLOBALS['merchantId'];
$notifyUrl = $GLOBALS['notifyUrl'];
$notifyEmail = $GLOBALS['notifyEmail'];
$notifyMobile = $GLOBALS['notifyMobile'];
$requestId = date( "YmdHis" );
$signType = "MD5";
$type = "DODIRECTPAYMENT";		
$version = "1.0.1";
//$productName = mb_convert_encoding($title, 'GBK', 'UTF-8');
//报文体数据
$allowNote = "0";
$amount = strval($_REQUEST["amount"] * 100);;
$authorizeMode = "WEB";
$banks = "";
$currency = mb_convert_encoding($_REQUEST["currency"], 'GBK', 'UTF-8');
$deliverFlag = "0";	
$invoiceFlag = "0";		
$orderDate = date( "Ymd" );
$orderId = mb_convert_encoding($_REQUEST["orderId"], 'GBK', 'UTF-8'); //商户订单号
$pageStyle = "";
$period = "2";		
$periodUnit = "2";
$productDesc = mb_convert_encoding($_REQUEST["productDesc"], 'GBK', 'UTF-8');
$productId = mb_convert_encoding($_REQUEST["productId"], 'GBK', 'UTF-8');
$productName = mb_convert_encoding($_REQUEST["productName"], 'GBK', 'UTF-8');
$reserved = "reserved";
$userToken = "";
$signKey = $GLOBALS['signKey'];
$source = $callbackUrl . $ipAddress . $merchantId . $notifyUrl . $notifyEmail . $notifyMobile . $requestId . $signType . $type . $version . $allowNote	. $amount . $authorizeMode . $banks . $currency . $deliverFlag . $invoiceFlag 	. $orderDate . $orderId . $pageStyle . $period . $periodUnit . $productDesc . $productId . $productName . $reserved . $userToken;
$hash = hmac("",$source);
$hmac = hmac($signKey,$hash);
$requestData = array();
$requestData["callbackUrl"] = $callbackUrl;
$requestData["hmac"] = $hmac;
$requestData["ipAddress"] = $ipAddress;
$requestData["merchantId"] = $merchantId;
$requestData["notifyUrl"] = $notifyUrl;
$requestData["notifyEmail"] = $notifyEmail;
$requestData["notifyMobile"] = $notifyMobile;
$requestData["requestId"] = $requestId;
$requestData["signType"] = $signType;
$requestData["type"] = $type;
$requestData["version"] = $version;
$requestData["allowNote"] = $allowNote;
$requestData["amount"] = $amount;
$requestData["authorizeMode"] = $authorizeMode;
$requestData["banks"] = $banks;
$requestData["currency"] = $currency;
$requestData["deliverFlag"] = $deliverFlag;
$requestData["invoiceFlag"] = $invoiceFlag;
$requestData["orderDate"] = $orderDate;
$requestData["orderId"] = $orderId;
$requestData["pageStyle"] = $pageStyle;
$requestData["period"] = $period;
$requestData["periodUnit"] = $periodUnit;
$requestData["productDesc"] = $productDesc;
$requestData["productId"] = $productId;
$requestData["productName"] = $productName;
$requestData["reserved"] = $reserved;
$requestData["userToken"] = $userToken;		
//print_r($requestData);
//exit;
$sTotalString = POSTDATA($url,$requestData);
$recv = $sTotalString["MSG"];
$recvArray = parseRecv($recv);
		
//校验签名
$r_hmac = $recvArray["hmac"];
$r_merchantId = $recvArray["merchantId"];
$r_payNo = $recvArray["payNo"];
$r_requestId = $recvArray["requestId"];
$r_returnCode = $recvArray["returnCode"];
$r_message = $recvArray["message"];
$r_signType = $recvArray["signType"];
$r_type = $recvArray["type"];
$r_version = $recvArray["version"];
$sessionId = $recvArray["SESSIONID"];
$r_source = $r_merchantId.$r_payNo.$r_requestId.$r_returnCode.$r_message.$r_signType.$r_type.$r_version.$sessionId;
$r_hash = hmac("",$r_source);
$r_newhmac = hmac($signKey,$r_hash);
//echo $r_returnCode;
//echo "<br />";
//echo $r_message ;
//exit;
if($r_hmac != $r_newhmac )
	{
	echo("验证签名失败！");
    die();
	}
	else
	{						
	$newUrl = $GLOBALS["tokenRedirectUrl"];

?>
<!DOCTYPE HTML PUBLIC "-W3CDTD HTML 4.01 TransitionalEN">
<html>
<head>
</head>
<body onload="Javascript:document.f1.submit();">
Go to cmpay.10086.com...
<form name="f1" action="<?php echo $newUrl ?>" method="post">
<input type="hidden" name="SESSIONID" value="<?php echo $sessionId ?>">
<input type="submit" value="go"> 
</form>
</body>
</html>
<?php } ?>
