<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
need_manager();
need_auth('team');
$now = time();
$expire = mktime(0,0,0,date('m'),date('d')+7,date('Y'));
$t = strtotime(date('Y-m-d 00:00:00'));
$condition = array(
	'expire_time >= ' . $now,
	'expire_time <= ' . $expire,
	'delivery <> "express" ',
	'send_time=0 or  send_time > ' .$t,
);
$teams = DB::LimitQuery('team', array(
			'condition' => $condition,
			'order' => 'order by expire_time asc',
			));

function notconsume($team_id){
	$count = Table::Count('coupon', array(
		'consume' => 'N',
		'team_id' => $team_id,
	));
	return $count;
}
include template('manage_misc_expire');