<?php
function mail_custom($emails=array(), $subject, $message) {
	global $INI;
	$encoding = $INI['mail']['encoding'] ? $INI['mail']['encoding'] : 'UTF-8';
	settype($emails, 'array');

	$options = array(
		'contentType' => 'text/html',
		'encoding' => $encoding,
	);

	$from = $INI['mail']['from'];
	$to = array_shift($emails);
	if ($INI['mail']['mail']=='mail') {
		Mailer::SendMail($from, $to, $subject, $message, $options, $emails);
	} else {
		Mailer::SmtpMail($from, $to, $subject, $message, $options, $emails);
	}
}

function mail_sign($user) {
	global $INI;
	$encoding = $INI['mail']['encoding'] ? $INI['mail']['encoding'] : 'UTF-8';
	if ( empty($user) ) return true;
	$from = $INI['mail']['from'];
	$to = $user['email'];

	$vars = array( 'user' => $user,);
	$message = render('mail_sign_verify', $vars);
	$subject = '感谢注册'.$INI['system']['sitename'].'，请验证Email以获取更多服务';

	$options = array(
		'contentType' => 'text/html',
		'encoding' => $encoding,
	);
	if ($INI['mail']['mail']=='mail') {
		Mailer::SendMail($from, $to, $subject, $message, $options);
	} else {
		Mailer::SmtpMail($from, $to, $subject, $message, $options);
	}
}

function mail_sign_id($id) {
	$user = Table::Fetch('user', $id);
	mail_sign($user);
}

function mail_sign_email($email) {
	$user = Table::Fetch('user', $email, 'email');
	mail_sign($user);
}

function mail_repass($user) {
	global $INI;
	$encoding = $INI['mail']['encoding'] ? $INI['mail']['encoding'] : 'UTF-8';
	if ( empty($user) ) return true;
	$from = $INI['mail']['from'];
	$to = $user['email'];

	$vars = array( 'user' => $user,);
	$message = render('mail_repass', $vars);
	$subject = $INI['system']['sitename'] . '重设密码';

	$options = array(
		'contentType' => 'text/html',
		'encoding' => $encoding,
	);
	if ($INI['mail']['mail']=='mail') {
		Mailer::SendMail($from, $to, $subject, $message, $options);
	} else {
		Mailer::SmtpMail($from, $to, $subject, $message, $options);
	}
}

function mail_order($order,$user,$partner) {
    global $INI;
    $encoding = $INI['mail']['encoding'] ? $INI['mail']['encoding'] : 'UTF-8';
    if ( empty($order) ) return true;
    $from = $INI['mail']['from'];
	//exit(print_r($from));
    $to = '';
	
    $vars = array('order' =>$order,'user'=>$user,'partner'=>$partner);
	
    $message = render('mail_order', $vars);
	//exit(print_r($order));
    $subject = $INI['system']['sitename'] . '订单提醒';

    $options = array(
        'contentType' => 'text/html',
        'encoding' => $encoding,
    );
	
    if ($INI['mail']['mail']=='mail') {
        Mailer::SendMail($from, $to, $subject, $message, $options);
    } else {
        Mailer::SmtpMail($from, $to, $subject, $message, $options);
    }
}


function mail_subscribe($city, $team, $partner, $subscribe) 
{
	global $INI;
	$encoding = $INI['mail']['encoding'] ? $INI['mail']['encoding'] : 'UTF-8';
	$week = array('日','一','二','三','四','五','六');
	$today = date('Y年n月j日 星期') . $week[date('w')];
	$vars = array(
		'today' => $today,
		'team' => $team,
		'city' => $city,
		'subscribe' => $subscribe,
		'partner' => $partner,
		'help_email' => $INI['mail']['helpemail'],
		'help_mobile' => $INI['mail']['helpphone'],
		'notice_email' => $INI['mail']['reply'],
	);
	$message = render('mail_subscribe_team', $vars);
	$options = array(
		'contentType' => 'text/html',
		'encoding' => $encoding,
	);
	$from = $INI['mail']['from'];
	$to = $subscribe['email'];
	$subject = $INI['system']['sitename'] . "今日团购：{$team['title']}";

	if ($INI['mail']['mail']=='mail') {
		Mailer::SendMail($from, $to, $subject, $message, $options);
	} else {
		Mailer::SmtpMail($from, $to, $subject, $message, $options);
	}
}


function mail_invitation($emails, $content, $name) {
	global $INI;
	if(empty($emails)) return;

	$emails = preg_split('/[\s,]+/', $emails, -1, PREG_SPLIT_NO_EMPTY);
	$subject = "您的好友[{$name}]邀请您注册{$INI['system']['sitename']}";
	$vars = array( 
			'name' => $name,
			'content' => $content,
			);
	$message = render('mail_invite', $vars);

	$step = ceil(count($emails)/20);
	for($i=0; $i<$step; $i++) {
		$offset = $i * 20;
		$tos = array_slice($emails, $offset, 20);
		mail_custom($tos, $subject, $message);
	}
	return true;
}

function mail_subscribemulti($teams,$subscribe) 
{
	global $INI;
	$encoding = $INI['mail']['encoding'] ? $INI['mail']['encoding'] : 'UTF-8';
	$week = array('日','一','二','三','四','五','六');
	$today = date('Y年n月j日 星期') . $week[date('w')];
	$first = array_shift($teams);
	$vars = array(
		'today' => $today,
		'first' => $first,
		'teams' => $teams,
		'subscribe' => $subscribe,
		'help_email' => $INI['mail']['helpemail'],
		'help_mobile' => $INI['mail']['helpphone'],
		'notice_email' => $INI['mail']['reply'],
	);
	$message = render('mail_subscribe_multiteam', $vars);
	$options = array(
		'contentType' => 'text/html',
		'encoding' => $encoding,
	);
	$from = $INI['mail']['from'];
	$to = $subscribe['email'];
	$subject = $INI['system']['sitename'] . "今日团购：{$team['title']}";

	if ($INI['mail']['mail']=='mail') {
		Mailer::SendMail($from, $to, $subject, $message, $options);
	} else {
		Mailer::SmtpMail($from, $to, $subject, $message, $options);
	}
}
