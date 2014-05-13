<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();

$usage = array( 'Y' => '已用', 'N' => '未用' );
$condition = array();

/* filter */
if (strval($_GET['tid'])!=='') {
	$tid = abs(intval($_GET['tid']));
	$condition['id'] = $tid;
}
if (strval($_GET['state'])) {
	$state = strval($_GET['state']);
	$condition['consume'] = $state;
}
/* end */

$count = Table::Count('paycard', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 50);
if ( strval($_GET['download'])) { $offset = 0; $pagesize = 100000; }

$cards = DB::LimitQuery('paycard', array(
	'condition' => $condition,
	'size' => $pagesize,
	'offset' => $offset,
	'order' => 'ORDER BY consume DESC, expire_time DESC',
));
$user_ids = Utility::GetColumn($cards, 'user_id');
$users = Table::Fetch('user', $user_ids);

if ( strval($_GET['download'])) {
	$name = "paycard_{$state}_".date('Ymd');
	$kn = array(
		'id' => '密码',
		'value' => '金额',
	);
	foreach($cards AS $cid => $one) {
		$one['id'] = '#'.$one['id'];
		$one['value'] = moneyit($one['value']);
        $cards[$cid] = $one;
	}
	down_xls($cards, $kn, $name);
}

include template('manage_coupon_paycard');
