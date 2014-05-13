<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$condition = array(
    'state' => 'pay',
	'comment_display' => 'Y',
	"comment_content != 'Null'",
);
$count = Table::Count('order', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 8);

$orders = DB::LimitQuery('order', array(
	'condition' => $condition,
	'order' => 'ORDER BY comment_time DESC, id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));
$user_ids = Utility::GetColumn($orders, 'user_id');
$users = Table::Fetch('user', $user_ids);

$team_ids = Utility::GetColumn($orders, 'team_id');
$teams = Table::Fetch('team', $team_ids);


$pagetitle = '买家点评';
include template('team_comment');
