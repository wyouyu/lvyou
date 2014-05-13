<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));

$r = udecode($_GET['r']);
$tid = strval($_GET['tid']);
$cate = strval($_GET['cate']);
$like = strval($_GET['like']);

if ( $action == 'r' ) {
    $order = Table::Fetch('order',$id);
	log_admin('misc', '删除订单点评',$order['comment_content']);
	Table::UpdateCache('order',$id,array('comment_content'=>'Null'));
	redirect($r);
}
if ( $action == 'none' ) {
	Table::UpdateCache('order',$id,array('comment_display'=>'N'));
	redirect($r);
}
if ( $action == 'block' ) {
	Table::UpdateCache('order',$id,array('comment_display'=>'Y'));
	redirect($r);
}

$condition = array("comment_time > 0 AND comment_content <> 'Null'");
if ($tid) { $condition['team_id'] = $tid; }
if ($cate) { $condition['comment_grade'] = $cate; }
if ($like) { 
	$condition[] = "commnet_content like '%".mysql_escape_string($like)."%'";
}

$count = Table::Count('order', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$orders = DB::LimitQuery('order', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));

$user_ids = Utility::GetColumn($orders, 'user_id');
$users = Table::Fetch('user', $user_ids);

include template('manage_misc_comment');
