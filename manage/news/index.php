<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('news');

$now = time();
$condition = array();
$count = Table::Count('news', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$news = DB::LimitQuery('news', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));


$selector = 'index';
include template('manage_news_index');
