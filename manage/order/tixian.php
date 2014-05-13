<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();

if($_GET['action']=='do')
{
	$tid =intval($_GET['tid']);
	$ti = Table::Fetch('tixian',$tid);

	Table::UpdateCache('tixian', $tid, array(
					'status' => 1,
					));
	showmessage('提现成功！','/manage/order/tixian.php');
}


$condition = array();
$count = Table::Count('tixian', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);
$tixian = DB::LimitQuery('tixian', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));
$user_ids = Utility::GetColumn($tixian, 'uid');
$users = Table::Fetch('user', $user_ids);




$current ='tixian';
include template('manage_account_tixian');
