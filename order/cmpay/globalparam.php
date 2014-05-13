<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');


$GLOBALS['signKey']=$INI['cmpay']['sec'];//商户密钥，用于md5签名校验用的
$GLOBALS['merchantId']=$INI['cmpay']['mid'];//商户id，商户在手机支付注册的唯一标识

$GLOBALS['commUrl']="http://ipos.10086.cn/ips/APITrans";//申请令牌（sessionid）地址
$GLOBALS['tokenReqUrl']="http://ipos.10086.cn/ips/APITrans2";//得到令牌（sessionid）后提交的地址
$GLOBALS['tokenRedirectUrl']="http://ipos.10086.cn/ips/FormTrans3";
//短信支付提交的地址
$GLOBALS['callbackUrl']=$INI['system']['wwwprefix'] . '/order/cmpay/backurl.php';//商户提交的页面返回地址（商户的地址）
$GLOBALS['notifyUrl']=$INI['system']['wwwprefix'] . '/order/cmpay/notifyurl.php';//商户提交的后台返回地址（商户的地址）
$GLOBALS['notifyEmail']="abc@sohu.com";
$GLOBALS['notifyMobile']="13800000000";
$GLOBALS['mobile']="";
?>
