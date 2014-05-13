<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
require_once(WWW_ROOT . '/order/yeepay/yeepayCommon.php');
	
$p1_MerId = $INI['yeepay']['mid'];
$merchantKey = $INI['yeepay']['sec'];

$return = getCallBackValue($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);

$bRet = CheckHmac($r0_Cmd,$p1_MerId,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac,$merchantKey);


if($bRet){
	if($r1_Code=="1"){
		list($_, $order_id, $city_id, $_) = explode('-', $r6_Order, 4);

		if($r9_BType=="1"){

			/* charge */
			if ( $_ == 'charge' ) {
				@list($_, $user_id, $create_time, $_) = explode('-', $r6_Order, 4);
				$service = 'yeepay';
				if(ZFlow::CreateFromCharge($r3_Amt,$user_id,$create_time,$service)) {
					Session::Set('notice', "在线充值{$r3_Amt}元成功！");
				};
				redirect(WEB_ROOT . '/credit/index.php');
			}
			/* end charge */

			$currency = 'CNY';
			$service = 'yeepay';
			$bank ='易宝';
			ZOrder::OnlineIt($order_id, $r6_Order, $r3_Amt, $currency, $service, $bank);
			redirect(WEB_ROOT . "/order/pay.php?id={$order_id}");

		}
		else if ($r9_BType=="2") {

			//如果需要应答机制则必须回写流,以success开头,大小写不敏感.
			/* charge */

			if ( $_ == 'charge' ) {
				@list($_, $order_id, $create_time, $_) = explode('-', $r6_Order, 4);
				$service = 'yeepay';
				if(ZFlow::CreateFromCharge($r3_Amt,$order_id,$create_time,$service)) {
					Session::Set('notice', "在线充值{$v_amount}元成功！");
				};
				redirect(WEB_ROOT . '/credit/index.php');
			}
			/* end charge */

			$currency = 'CNY';
			$service = 'yeepay';
			$bank = '易宝';

			ZOrder::OnlineIt($order_id, $r6_Order, $r3_Amt, $currency, $service, $bank);
			//redirect(WEB_ROOT . "/order/pay.php?id={$order_id}");
			ob_end_clean();
			die('SUCCESS');
		}
	}

}

die('ILLEGAL ORDER INFOMATION');
