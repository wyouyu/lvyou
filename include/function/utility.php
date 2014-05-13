<?php
function get_city($ip=null) {
	$cities = option_category('city', false, true);
	$ip = ($ip) ? $ip : Utility::GetRemoteIP();
	$location = ip_location_youdao($ip);
	if ( $location ) {
		foreach( $cities AS $one) {
			if ( FALSE !== strpos($location, $one['name']) ) {
				return $one;
			}
		}
	}
	return array();
}

function ip_location_baidu($ip) {
	$u = "http://open.baidu.com/ipsearch/s?wd={$ip}&tn=baiduip";
	$r = mb_convert_encoding(Utility::HttpRequest($u), 'UTF-8', 'GBK');
	preg_match('#来自：<b>(.+)</b>#Ui', $r, $m);
	return strval($m[1]);
}

function ip_location_youdao($ip) {
	$u = "http://www.youdao.com/smartresult-xml/search.s?type=ip&q={$ip}";
	$r = mb_convert_encoding(Utility::HttpRequest($u), 'UTF-8', 'GBK');
	preg_match("#<location>(.+)</location>#Ui", $r, $m);
	return strval($m[1]);
}

function mail_zd($email) {
	global $option_mail;
	if ( ! Utility::ValidEmail($email) ) return false;
	preg_match('#@(.+)$#', $email, $m);
	$suffix = strtolower($m[1]);
	return $option_mail[$suffix];
}

function nanooption($string) {
	if ( preg_match_all('#{(.+)}#U', $string, $m) ){
		return $m[1];
	}
	return array();
}

global $striped_field;
$striped_field = array(
		'username',
		'realname',
		'name',
		'tilte',
		'email',
		'address',
		'mobile',
		'url',
		'logo',
		'contact',
		);

global $option_gender;
$option_gender = array(
		'F' => '女',
		'M' => '男',
		);
global $option_pay;
$option_pay = array(
		'pay' => '已支付',
		'unpay' => '未支付',
		);
global $option_service;
$option_service = array(
		'alipay' => '支付宝',
		'tenpay' => '财付通',
        'sdopay' => '盛付通',
		'chinabank' => '网银在线',
		'paypal' => 'Paypal',
		'yeepay' => '易宝',
		'cash' => '现金支付',
        'cmpay' => '手机支付',
		'gopay' => '国付宝',
        'credit' => '余额付款',
		'other' => '其他',
		);
global $option_delivery;
$option_delivery = array(
		'express' => '快递',
		'voucher' => '商户券',
		'coupon' => '券',
		'pickup' => '自取',
		);
global $option_flow;
$option_flow = array(
		'buy' => '购买',
		'invite' => '邀请',
		'store' => '充值',
		'withdraw' => '提现',
		'coupon' => '返利',
		'refund' => '退款',
		'register' => '注册',
		'charge' => '充值',
		'daysign' => '每日签到',
		);
global $option_mail;
$option_mail = array(
		'gmail.com' => 'https://mail.google.com/',
		'163.com' => 'http://mail.163.com/',
		'126.com' => 'http://mail.126.com/',
		'qq.com' => 'http://mail.qq.com/',
		'sina.com' => 'http://mail.sina.com/',
		'sohu.com' => 'http://mail.sohu.com/',
		'yahoo.com.cn' => 'http://mail.yahoo.com.cn/',
		'yahoo.com' => 'http://mail.yahoo.com/',
		);
global $option_cond;
$option_cond = array(
		'Y' => '以购买成功人数成团',
		'N' => '以产品购买数量成团',
		);
global $option_open;
$option_open = array(
		'Y' => '开放展示',
		'N' => '关闭展示',
		);
global $option_buyonce;
$option_buyonce = array(
		'Y' => '仅购买一次',
		'N' => '可购买多次',
		);
global $option_teamtype;
$option_teamtype = array(
		'normal' => '团购项目',
		'seconds' => '秒杀项目',
		'goods' => '热销商品',
		);
global $option_yn;
$option_yn = array(
		'Y' => '是',
		'N' => '否',
		);
global $option_alipayitbpay;
$option_alipayitbpay = array(
		'1h' => '1小时',
		'2h' => '2小时',
		'3h' => '3小时',
		'1d' => '1天',
		'3d' => '3天',
		'7d' => '7天',
		'15d' => '15天',
		);
global $option_commentgrade;
$option_commentgrade = array(
		'good' => '满意',
		'none' => '一般',
		'bad' => '失望',
		);
global $option_commentwantmore;
$option_commentwantmore = array(
		'Y' => '是',
		'N' => '否',
		);
global $option_timezone;
$option_timezone = array(
	'Etc/GMT+12' => 'GMT-12:00',
	'Etc/GMT+11' => 'GMT-11:00',
	'Etc/GMT+10' => 'GMT-10:00',
	'Etc/GMT+9' => 'GMT-09:00',
	'Etc/GMT+8' => 'GMT-08:00',
	'Etc/GMT+7' => 'GMT-07:00',
	'Etc/GMT+6' => 'GMT-06:00',
	'Etc/GMT+5' => 'GMT-05:00',
	'Etc/GMT+4' => 'GMT-04:00',
	'Etc/GMT+3' => 'GMT-03:00',
	'Etc/GMT+2' => 'GMT-02:00',
	'Etc/GMT+1' => 'GMT-01:00',
	'Etc/GMT+0' => 'GMT+00:00',
	'Etc/GMT-1' => 'GMT+01:00',
	'Etc/GMT-2' => 'GMT+02:00',
	'Etc/GMT-3' => 'GMT+03:00',
	'Etc/GMT-4' => 'GMT+04:00',
	'Etc/GMT-5' => 'GMT+05:00',
	'Etc/GMT-6' => 'GMT+06:00',
	'Etc/GMT-7' => 'GMT+07:00',
	'Etc/GMT-8' => 'GMT+08:00',
	'Etc/GMT-9' => 'GMT+09:00',
	'Etc/GMT-10' => 'GMT+10:00',
	'Etc/GMT-11' => 'GMT+11:00',
	'Etc/GMT-12' => 'GMT+12:00',
	'Etc/GMT-13' => 'GMT+13:00',
	'Etc/GMT-14' => 'GMT+14:00',
);
global $option_guarantee;
$option_guarantee = array(
		'Y' => '担保交易',
		'N' => '即时交易',
        'S' => '双功能',
		);
global $option_tenpayguarantee;
$option_tenpayguarantee = array(
		'Y' => '担保交易',
		'N' => '即时到账',
		);
global $option_guaranteesuccess;
$option_guaranteesuccess = array(
		'Y' => '支付宝成功放款',
		'N' => '买家付款支付宝',
		);
global $option_autosendgoods;
$option_autosendgoods = array(
		'Y' => '是',
		'N' => '否',
		);
global $option_alifast;
$option_alifast = array(
        'N' => '关闭快捷登陆',
		'Y' => '使用快捷登陆',
		);
global $option_aliaddress;
$option_aliaddress = array(
        'N' => '关闭支付宝物流',
		'Y' => '使用支付宝物流',
		);
global $option_checkexpress;
$option_checkexpress = array(
		'pay' => '已发货',
		'unpay' => '未发货',
		);
global $option_editor;
$option_editor = array(
		'kind' => 'kindEditor',
		'xh' => 'xhEditor',
		);
