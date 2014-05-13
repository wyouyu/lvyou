<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

$amount=$_POST["Amount"];
$payamount=$_POST["PayAmount"];
$orderid=$_POST["OrderNo"];
$serialno=$_POST["SerialNo"];
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
//echo $ok;
//echo "<br/>";

if ( $_ == 'charge' ) {
	  if ( $ok == 1 ) {
		$pay = Table::Fetch('pay', $orderid);
        if ( $pay ) {
		Session::Set('notice', "盛付通充值{$amount}元成功！");
		}
	}
	redirect(WEB_ROOT . '/credit/index.php');
}

if($ok == 1) {
	  $order = Table::Fetch('order', $order_id);
	  if ( $order['state'] == 'pay' ) {
		Session::Set('notice', "购买成功！");
	 }
     redirect( WEB_ROOT . "/order/pay.php?id={$order_id}");
}
elseif ($ok == 0) {
   echo "bad";
   include template('order_return_error');
} else {
   echo "ugly, error checking signature";
   include template('order_return_error');
}

?>
