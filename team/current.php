<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$daytime = time();
$condition = array( 
		"begin_time <  {$daytime}",
		'team_type' => 'normal',	//kxx 增加
		"end_time >= {$daytime}",
		);
$city_id = abs(intval($city['id']));
$condition[] = "(city_ids like '%@{$city_id}@%' or city_ids like '%@0@%') or (city_ids = '' and city_id in(0,{$city_id}))";

$count = Table::Count('team', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 1000);
$teams = DB::LimitQuery('team', array(
			'condition' => $condition,
			'order' => 'ORDER BY begin_time DESC, id DESC',
			'size' => $pagesize,
			'offset' => $offset,
			));
foreach($teams AS $id=>$one){
	team_state($one);
	if (!$one['close_time']) $one['picclass'] = 'isopen';
	if ($one['state']=='soldout') $one['picclass'] = 'soldout';
	$teams[$id] = $one;
}

$pagetitle = '当期团购';
include template('team_current');
