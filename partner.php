<?php
require_once(dirname(__FILE__) . '/app.php');

$id = abs(intval($_GET['id']));
if (!$id || !$partner = Table::FetchForce('partner', $id) ) {
	redirect( WEB_ROOT . '/partner/index.php');
}
$pagetitle = $partner['title'];

$view = strtolower(strval($_GET['view']));
$view = (!in_array($view, array('team','comment'))) ? 'comment' : $view;

if ( 'team' == $view || true) {
	$daytime = time();
	$condition = array( 
			'partner_id' => $id,
			"begin_time <  {$daytime}",
			'OR' => array(
				"now_number >= min_number",
				"end_time > {$daytime}",
				),      
			);
	$teams = DB::LimitQuery('team', array(
				'condition' => $condition,
				'order' => 'ORDER BY begin_time DESC, id DESC',
				));

	$team_count = count($teams);
	$join_number = 0;
	foreach($teams AS $k=>$one){
		team_state($one);
		if (!$one['close_time']) $one['picclass'] = 'isopen';
		if ($one['state']=='soldout') $one['picclass'] = 'soldout';
		$teams[$k] = $one;
		$join_number += $one['now_number'];
		$save_number += $one['now_number'] * ($one['market_price'] - $one['team_price']);
	}
}

if ( 'comment' == $view ) {
	$cc = array(
			'partner_id' => $id,
			'comment_display' => 'Y',
			'comment_time > 0',
			);
	$comments = DB::LimitQuery('order', array(
				'condition' => $cc,
				));
	foreach( $comments AS $k=>$v) {
		$v['grade'] = 'A';
		$v['grade'] = $v['comment_grade']=='none' ? 'P' : $v['grade'];
		$v['grade'] = $v['comment_grade']=='bad' ? 'F' : $v['grade'];
		$v['comment'] = htmlspecialchars($v['comment_content']);
		$comments[$k] = $v;
	}
	$user_ids = Utility::GetColumn($comments, 'user_id');
	$users = $user_ids ? Table::Fetch('user', $user_ids) : array();
}

$comments_num = ($partner['comment_good'] + $partner['comment_bad'] + $partner['comment_none']);
$grades['A'] = $partner['comment_good'];
$grades['P'] = $partner['comment_none'];
$grades['F'] = $partner['comment_bad'];


include template('partner');
