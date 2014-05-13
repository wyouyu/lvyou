<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/inc.php');

$id = abs(intval($_GET['id']));

if (!$id || !$team = Table::Fetch('team', $id) ) {
	redirect( WEB_ROOT . '/team/index.php');
}

team_state($team);
$pagetitle = "{$INI['system']['abbreviation']}答疑 {$team['title']}";
$condition = array( 'length(comment)>0', 'type' => 'transfer', );

if(option_yes('teamask')) { $condition[] = 'team_id > 0'; } 
else { $condition['team_id'] = $id; }

/*pageit*/
$count = Table::Count('ask', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 10);
$asks = DB::LimitQuery('ask', array(
			'condition' => $condition,
			'order' => 'ORDER BY id DESC',
			'size' => $pagesize,
			'offset' => $offset,
			));
/*endpage*/

$user_ids = Utility::GetColumn($asks, 'user_id');
$users = Table::Fetch('user', $user_ids);
include template('team_transfer');
