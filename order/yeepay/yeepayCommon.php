<?php
function getReqHmacString($p1_MerId,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse, $merchantKey)
{
	$p0_Cmd = 'Buy';
	$p9_SAF = '0';
	$sbOld = "";
	$sbOld = $sbOld.$p0_Cmd;
	$sbOld = $sbOld.$p1_MerId;
	$sbOld = $sbOld.$p2_Order;
	$sbOld = $sbOld.$p3_Amt;
	$sbOld = $sbOld.$p4_Cur;
	$sbOld = $sbOld.$p5_Pid;
	$sbOld = $sbOld.$p6_Pcat;
	$sbOld = $sbOld.$p7_Pdesc;
	$sbOld = $sbOld.$p8_Url;
	$sbOld = $sbOld.$p9_SAF;
	$sbOld = $sbOld.$pa_MP;
	$sbOld = $sbOld.$pd_FrpId;
	$sbOld = $sbOld.$pr_NeedResponse;

	return HmacMd5($sbOld, $merchantKey);
} 

function getCallbackHmacString($r0_Cmd,$p1_MerId,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$merchantKey)
{
	$sbOld = "";
	$sbOld = $sbOld.$p1_MerId;
	$sbOld = $sbOld.$r0_Cmd;
	$sbOld = $sbOld.$r1_Code;
	$sbOld = $sbOld.$r2_TrxId;
	$sbOld = $sbOld.$r3_Amt;
	$sbOld = $sbOld.$r4_Cur;
	$sbOld = $sbOld.$r5_Pid;
	$sbOld = $sbOld.$r6_Order;
	$sbOld = $sbOld.$r7_Uid;
	$sbOld = $sbOld.$r8_MP;
	$sbOld = $sbOld.$r9_BType;
	return HmacMd5($sbOld,$merchantKey);
}

function getCallBackValue(&$r0_Cmd,&$r1_Code,&$r2_TrxId,&$r3_Amt,&$r4_Cur,&$r5_Pid,&$r6_Order,&$r7_Uid,&$r8_MP,&$r9_BType,&$hmac)
{  
	$r0_Cmd		= $_REQUEST['r0_Cmd'];
	$r1_Code	= $_REQUEST['r1_Code'];
	$r2_TrxId	= $_REQUEST['r2_TrxId'];
	$r3_Amt		= $_REQUEST['r3_Amt'];
	$r4_Cur		= $_REQUEST['r4_Cur'];
	$r5_Pid		= $_REQUEST['r5_Pid'];
	$r6_Order	= $_REQUEST['r6_Order'];
	$r7_Uid		= $_REQUEST['r7_Uid'];
	$r8_MP		= $_REQUEST['r8_MP'];
	$r9_BType	= $_REQUEST['r9_BType']; 
	$hmac	    = $_REQUEST['hmac'];
	return null;
}

function CheckHmac($r0_Cmd,$p1_MerId,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac,$merchantKey)
{
	return ($hmac==getCallbackHmacString($r0_Cmd,$p1_MerId,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$merchantKey));
}


function HmacMd5($data,$key)
{
	$b = 64; // byte length for md5
	if (strlen($key) > $b) {
		$key = pack("H*",md5($key));
	}
	$key = str_pad($key, $b, chr(0x00));
	$ipad = str_pad('', $b, chr(0x36));
	$opad = str_pad('', $b, chr(0x5c));
	$k_ipad = $key ^ $ipad ;
	$k_opad = $key ^ $opad;

	return md5($k_opad . pack("H*",md5($k_ipad . $data)));
}
