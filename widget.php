<?php
require_once(dirname(__FILE__) . '/app.php');
$city_id = abs(intval($_GET['cityid']));
if(!$city_id) Utility::Redirect( WEB_ROOT . '/index.php' );
$now = time();
$condition = array( 
			'team_type' => 'normal',
			"begin_time < '{$now}'",
			"end_time > '{$now}'",
			);
$condition[] = "(city_ids like '%@{$city_id}@%' or city_ids like '%@0@%') or (city_ids = '' and city_id in(0,{$city_id}))";
//DB::Debug();
$teams = DB::LimitQuery('team', array(
				'condition' => $condition,
				'order' => 'ORDER BY `sort_order` DESC, `id` DESC',
				'size' => '3',
				));



include template('index_widget');
