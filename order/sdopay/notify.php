<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

$amount=$_POST["Amount"];
$payamount=$_POST["PayAmount"];
$orderid=$_POST["OrderNo"];
$serialno=$_POST["serialno"];
$status=$_POST["Status"];
$merid=$_POST["MerchantNo"];
$paychannel=$_POST["PayChannel"];
$discount=$_POST["Discount"];
$signtype=$_POST["SignType"];
$paytime=$_POST["PayTime"];
$ctype=$_POST["CurrencyType"];
$prono=$_POST["ProductNo"];
$prodesc=$_POST["ProductDesc"];
$remark1=$_POST["Remark1"];
$remark2=$_POST["Remark2"];
$ex=$_POST["ExInfo"];
$mac=$_POST["MAC"];

$signString=$amount."|".$payamount."|".$orderid."|".$serialno."|".$status."|".$merid."|".$paychannel."|".$discount."|".$signtype."|".$paytime."|".$ctype."|".$prono."|".$prodesc."|".$remark1."|".$remark2."|".$ex;

if($signtype=="2")//md5
{
   $md5key=$INI['sdopay']['sec'];
   $mac2 =md5($signString."|".$md5key);
   $ok = (strtoupper($mac)==strtoupper($mac2));
}

@list($_, $order_id, $city_id, $_) = explode('-', $orderid, 4);
if (Table::Fetch('pay', $orderid)) die('OK');
//echo $order_id;
//exit;
if ( $_ == 'charge' ) {
  if ($ok == 1) {
			@list($_, $user_id, $create_time, $_) = explode('-', $orderid, 4);
			ZFlow::CreateFromCharge($amount, $user_id, $create_time, 'sdopay',$serialno);
	
        die("OK");//必须输出且只能输出"OK" 
 	}  
}

if ($ok == 1) {
		$currency = 'CNY';
		$service = 'sdopay';
		$bank = '盛付通';
		ZOrder::OnlineIt($order_id, $orderid, $amount, $currency, $service, $bank,$serialno);
        die('OK');//必须输出且只能输出"OK"  
}
elseif ($ok == 0) {
    die('bad');
} else {
    die('ugly, error checking signature');
}
?>
