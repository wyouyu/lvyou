<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
$title = strval($_GET['title']);
$type = strval($_GET['type']);

$condition = array( 'team_id > 0',);

/* filter */
if ($title) { 
	$t_con[] = "title like '%".mysql_escape_string($title)."%'";
	$teams = DB::LimitQuery('team', array(
				'condition' => $t_con,
				));
	$team_ids = Utility::GetColumn($teams, 'id');
	if ( $team_ids ) {
		$condition['team_id'] = $team_ids;
	} else { $title = null; }
}
if ($type) $condition['type'] = $type;
/* end filter */

$count = Table::Count('ask', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$asks = DB::LimitQuery('ask', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));

$user_ids = Utility::GetColumn($asks, 'user_id');
$team_ids = Utility::GetColumn($asks, 'team_id');

$users = Table::Fetch('user', $user_ids);
$teams = Table::Fetch('team', $team_ids);

$option_ask = array(
		'ask' => '团购问答',
		'transfer' => '求购转让',
		);

include template('manage_misc_ask');
