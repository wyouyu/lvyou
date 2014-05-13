<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();

$usage = array( 'Y' => '已用', 'N' => '未用' );
$condition = array();

/* filter */
if (strval($_GET['pid'])!=='') {
	$pid = abs(intval($_GET['pid']));
	$condition['partner_id'] = $pid;
}
if (strval($_GET['code'])) {
	$code = strval($_GET['code']);
	$condition[] = "code LIKE '%".mysql_escape_string($code)."%'";
}
if (strval($_GET['state'])) {
	$state = strval($_GET['state']);
	$condition['consume'] = $state;
}
if (strval($_GET['tid'])!=='') {
	$tid = abs(intval($_GET['tid']));
	$state = 'Y'; $pid = ''; $code = '';
	$condition = array();
	$condition['team_id'] = $tid;
}
/* end */

$count = Table::Count('card', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 50);
if ( strval($_GET['download'])) { $offset = 0; $pagesize = 100000; }

$cards = DB::LimitQuery('card', array(
	'condition' => $condition,
	'size' => $pagesize,
	'offset' => $offset,
	'order' => 'ORDER BY begin_time DESC, end_time DESC',
));

$partner_ids = Utility::GetColumn($cards, 'partner_id');
$partners = Table::Fetch('partner', $partner_ids);

if ( strval($_GET['download'])) {
	$name = "card_{$state}_".date('Ymd');
	$kn = array(
		'id' => '编号',
		'credit' => '面额',
		'card' => '实际抵用金额',
	);
	$order_ids = array_unique(Utility::GetColumn($cards, 'order_id'));
	$orders = Table::Fetch('order', $order_ids);
	foreach($cards AS $cid => $one) {
		$one['id'] = '#'.$one['id'];
		$one['card'] = moneyit($orders[$one['order_id']]['card']);
		$cards[$cid] = $one;
	}
	down_xls($cards, $kn, $name);
}

include template('manage_coupon_card');
