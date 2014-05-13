<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');
$usercount = Table::Count('user', array(" mobile <> '' ","id <> '1' "));

if ($_POST ) {
	$pertask = intval($_POST['pertask']);
	$current = $_POST['current'] ? intval($_POST['current']) : 0;
	if($pertask >'300'){
		Session::Set('error', "每次发送量不能大于300个手机号");
		redirect( WEB_ROOT + '/manage/market/smsall.php' );
	}
	$content = trim(strval($_POST['content']));
	$search_condition = array();
	$search_condition[] =" id <> '1' ";
	$search_condition[] =" mobile <> '' ";
	$uids = searchusers($search_condition, $pertask, $current);
	$continue = FALSE;
	if($uids) {
		$phones = implode(',', $uids);
		$ret = sms_send($phones, $content);
		if (  $ret===true ) {
			$continue = TRUE;
		}else{
			Session::Set('notice', "发送短信失败，错误码：{$ret}");
			redirect( WEB_ROOT + '/manage/market/smsall.php' );	
		}
	}
	if($continue) {
		$last = $current;
		$current = $current + $pertask;
		$sms_detail = array(
			'last' => $last,
			'current' => $current,
			'pertask' => $pertask,
			'content' => $content,
			'method' => 'POST' ,
			'action' => "smsall.php",
		);
		die(include template('manage_market_smscon'));
	}else{
		Session::Set('notice', "发送短信成功.");
		redirect( WEB_ROOT + '/manage/market/smsall.php' );
	}
 
    
	$phones = implode(',', $phones);
	
}

 
function searchusers($condition, $limit=300, $start=0) {
	
	$users = DB::LimitQuery('user', array(
				'condition' => $condition,
				'order' => 'ORDER BY `id` DESC',
				'size' => $limit,
				'offset' => $start,
				));
	$mobiles = Utility::GetColumn($users, 'mobile');
	return $mobiles;
	
}
function dimplode($array) {
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return 0;
	}
}

include template('manage_market_smsall');
