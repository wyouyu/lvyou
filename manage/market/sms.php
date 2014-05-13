<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

if ( $_POST ) {
	$phones = preg_split('/[\s,]+/', $_POST['phones'], -1, PREG_SPLIT_NO_EMPTY);
	$content = trim(strval($_POST['content']));
	$phone_count = count($phones);
	$phones = implode(',', $phones);
	$ret = sms_send($phones, $content);
	if ( $ret===true ) {
		Session::Set('notice', "发送短信成功，发送量：{$phone_count}");
		redirect( WEB_ROOT + '/manage/market/sms.php' );
	}
	Session::Set('notice', "发送短信失败，错误码：{$ret}");
}

include template('manage_market_sms');
