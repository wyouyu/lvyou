<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('team');

$now = time();
$condition = array(
	'system' => 'Y',
	"now_number < min_number",
	"end_time < $now",
);

/* filter start */
$team_type = strval($_GET['team_type']);
if ($team_type) { $condition['team_type'] = $team_type; }
$team_id = abs(intval($_GET['team_id']));
if ($team_id) {
	$condition['id'] = $team_id;
} else { $team_id = null; }
$team_key = strval($_GET['team_key']);
if ($team_key ) {
	$condition[] = "title LIKE '%".mysql_escape_string($team_key)."%'";
}
/* filter end */

$count = Table::Count('team', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$teams = DB::LimitQuery('team', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));
$cities = Table::Fetch('category', Utility::GetColumn($teams, 'city_id'));

$selector = 'failure';
include template('manage_team_index');
