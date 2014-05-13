<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
//$post_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
$post_url = "https://www.paypal.com/row/cgi-bin/webscr";

$result = fsockPost($post_url, $_POST);

// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];

if(eregi("VERIFIED", $result)) {
	$out_trade_no = $item_number;
	$total_fee = $payment_amount;

	@list($_, $order_id, $city_id, $_) = explode('-', $out_trade_no, 4);

	if ( $_ == 'charge' ) {
		@list($_,$user_id,$create_time,$_) = explode('-',$out_trade_no,4);
		ZFlow::CreateFromCharge($total_fee,$user_id,$create_time,'paypal');
	}
	else {
		$currency = $payment_currency;
		$service = 'paypal';
		$bank = 'PayPal';
		ZOrder::OnlineIt($order_id, $out_trade_no, $total_fee, $currency, $service, $bank);
	}
}

function fsockPost($url,$data) {
	//Parse url
	$web=parse_url($url);
	//build post string
	foreach($data as $i=>$v) {
		$postdata.= $i . "=" . urlencode($v) . "&";
	}
	$postdata.="cmd=_notify-validate";
	//Set the port number
	if($web['scheme'] == "https") {
		$web['port']="443";
		$ssl="ssl://";
	} else {
		$web['port']="80";
	}
	//Create paypal connection
	$fp = @fsockopen($ssl.$web['host'],$web['port'],$errnum,$errstr,30);
	//Error checking
	if(!$fp) { echo "$errnum: $errstr"; }
	//Post Data
	else {
		fputs($fp, "POST {$web['path']} HTTP/1.1\r\n");
		fputs($fp, "Host: {$web['host']}\r\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $postdata . "\r\n\r\n");
		//loop through the response from the server
		while(!feof($fp)) {
			$info[] = @fgets($fp, 1024);
		}
		//close fp - we are done with it
		fclose($fp);
		//break up results into a string
		$info = implode(",",$info);
	}
	return $info;
}
