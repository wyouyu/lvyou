<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$daytime = time();
$condition = array( 
		'team_type' => 'seconds',
		);
$city_id = abs(intval($city['id']));
$condition[] = "(city_ids like '%@{$city_id}@%' or city_ids like '%@0@%') or (city_ids = '' and city_id in(0,{$city_id}))";

/* filter */
$group_id = abs(intval($_GET['gid']));
if ($group_id) $condition['group_id'] = $group_id;

$count = Table::Count('team', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 10);
$teams = DB::LimitQuery('team', array(
	'condition' => $condition,
	'order' => 'ORDER BY begin_time DESC, id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));
foreach($teams AS $id=>$one){
	team_state($one);
	if ($one['state']=='none') $one['picclass'] = 'isopen';
	if ($one['state']=='soldout') $one['picclass'] = 'soldout';
	$teams[$id] = $one;
}

$pagetitle = '秒杀抢团';
include template('team_seconds');

function current_teamcategory($gid='0') {
	global $city;
	$a = array(
			'/team/seconds.php' => '所有',
			);
    $categorys = DB::LimitQuery('category', array(
		'condition' => array( 'zone' => 'group','fid' => '0','display' => 'Y' ),
		'order' => 'ORDER BY sort_order DESC, id DESC',
	));
	$categorys = Utility::OptionArray($categorys, 'id', 'name');
	foreach($categorys AS $id=>$name) {
		$a["/team/seconds.php?gid={$id}"] = $name;
	}
	$l = "/team/seconds.php?gid={$gid}";
	if (!$gid) $l = "/team/seconds.php";
	return current_link($l, $a, true);
}
