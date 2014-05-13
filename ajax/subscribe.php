<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
need_manager();
need_auth('market');

$action = strval($_GET['action']);

if ( 'mail' == $action ) {
	$now = time();
	$condition = array(
		'system' => 'Y',
		"end_time > {$now}",
	);
	$subcond = array();
	$subcount = Table::Count('subscribe', $subcond);
	$teams = DB::LimitQuery('team', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
	));
	$html = render('manage_ajax_dialog_mailmulti');
	json($html, 'dialog');
}
else if ( 'noticesubscribe' == $action ) {
	$tid = $_GET['tid'];
	if($tid==''){
		json('您没有选择项目', 'alert');
		return false;
	}
	$condition = array();
	$condition[] = "(id in($tid))";
	$teams = DB::LimitQuery('team', array(
				'condition' => $condition,
				'order' => 'ORDER BY `sort_order` DESC, `id` DESC',
				));
	$nid = abs(intval($_GET['nid']));
	$now = time();
	$interval = abs(intval($INI['mail']['interval']))? abs(intval($INI['mail']['interval'])):'5';
	
	$cond = array();
	$subs = DB::LimitQuery('subscribe', array(
				'condition' => $cond,
				'order' => 'ORDER BY id ASC',
				));
	if ( $subs ) {
		foreach($subs AS $one) {
			
			mail_subscribemulti($teams,$one);

			sleep($interval);
		}
	}else{
		json('未有订阅用户', 'alert');
	} 
	
	json('订阅邮件发送完毕', 'alert'); 
}
