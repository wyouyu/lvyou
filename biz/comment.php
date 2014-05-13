<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_partner();

$daytime = strtotime(date('Y-m-d'));
$partner_id = abs(intval($_SESSION['partner_id']));
$login_partner = Table::Fetch('partner', $partner_id);
$comments_num = ($login_partner['comment_good'] + $login_partner['comment_bad'] + $login_partner['comment_none']);

$title = strval($_GET['title']);
$grad = strval($_GET['grad']);
$condition = $t_con = array(
	'partner_id' => $partner_id,
);
/* stat num*/
$nowtime = time();
$c_condition = array( 
			'partner_id' => $partner_id,
			"begin_time <  {$nowtime}",
			'OR' => array(
				"now_number >= min_number",
				"end_time > {$nowtime}",
				),      
			);
$team_c = DB::LimitQuery('team', array(
				'condition' => $c_condition,
				'order' => 'ORDER BY begin_time DESC, id DESC',
				));
$team_count = count($team_c);
$join_number = 0;
	foreach($team_c AS $k=>$one){
		team_state($one);
		if (!$one['close_time']) $one['picclass'] = 'isopen';
		if ($one['state']=='soldout') $one['picclass'] = 'soldout';
		$team_c[$k] = $one;
		$join_number += $one['now_number'];
	}
/*end stat num*/

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

if ($grad) {
	switch(strtolower($grad)) {
		case 'good': $condition['comment_grade'] = 'good'; break;
		case 'none': $condition['comment_grade'] = 'none'; break;
		case 'bad' : $condition['comment_grade'] = 'bad';  default;
	}
}

/* end filter */

$count = Table::Count('order', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 10);

$orders = DB::LimitQuery('order', array(
	'condition' => $condition,
	'order' => 'ORDER BY team_id DESC, id ASC',
	'size' => $pagesize,
	'offset' => $offset,
));

$team_ids = Utility::GetColumn($orders, 'team_id');
$teams = Table::Fetch('team', $team_ids);

$user_ids = Utility::GetColumn($orders, 'user_id');
$users = Table::Fetch('user', $user_ids);
 
$option_grad = array(
	'good' => '满意',
	'none' => '一般',
	'bad' => '失望',
);

include template('biz_comment');
