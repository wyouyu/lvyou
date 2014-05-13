<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

$condition = array();
list($pagesize, $offset, $pagestring) = pagestring($count, 50);

$goods = DB::LimitQuery('goods', array(
	'condition' => $condition,
	'order' => 'ORDER BY sort_order DESC, id DESC',
	'offset' => $offset,
	'pagesize' => $pagesize,
));

include template('manage_credit_goods');
