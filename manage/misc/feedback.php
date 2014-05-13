<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));

$r = udecode($_GET['r']);
$cate = strval($_GET['cate']);
$like = strval($_GET['like']);

if ( $action == 'r' ) {
    $feed = Table::Fetch('feedback', $id);
	log_admin('misc', '删除意见反馈',$feed);
	Table::Delete('feedback', $id);
	redirect($r);
} else if ( $action == 'm' ) {
	Table::UpdateCache('feedback',$id,array('user_id'=>$login_user_id));
	redirect($r);
}

$condition = array();
if ($cate) { $condition['category'] = $cate; }
if ($like) { 
	$condition[] = "content like '%".mysql_escape_string($like)."%'";
}

$count = Table::Count('feedback', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$asks = DB::LimitQuery('feedback', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));

$user_ids = Utility::GetColumn($asks, 'user_id');
$users = Table::Fetch('user', $user_ids);

$feedcate = array('suggest'=>'意见反馈', 'seller'=>'商务合作');
include template('manage_misc_feedback');
