<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

if ( $_POST ) {
	$_POST['content'] = stripslashes($_POST['content']);

	$content = $_POST['content'];
	$emails = $_POST['emails'];
	$subject = $_POST['title'];

	$emails = preg_split('/[\s,]+/', $emails, -1, PREG_SPLIT_NO_EMPTY);
	$emails_array = array();
	foreach($emails AS $one) if(Utility::ValidEmail($one)) $emails_array[]=$one;
	$email_count = count($emails_array);

	$hostprefix = "http://{$_SERVER['HTTP_HOST']}/";
	$content = str_ireplace('href="/', "href=\"{$hostprefix}", $content);

	if ( !$email_count ) {
		Session::Set('error', '发送邮件错误：缺少合法的收件人邮件地址');
	} else {
		try {
			mail_custom($emails_array, $subject, $content);
			Session::Set('notice', "发送邮件成功，数量：{$email_count}");
			redirect( WEB_ROOT . '/manage/market/index.php' );
		}catch(Exception $e) {
			Session::Set('error', '发送邮件错误：'. $e->getMessage());
		}
	}
}

include template('manage_market_email');
