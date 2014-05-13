<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_partner();

$daytime = strtotime(date('Y-m-d'));
$partner_id = abs(intval($_SESSION['partner_id']));
$login_partner = Table::Fetch('partner', $partner_id);

$title = strval($_GET['title']);
$coupon = strval($_GET['coupon']);
$state = strval($_GET['state']);
$condition = $t_con = array(
	'partner_id' => $partner_id,
);

/* filter */
if ($title) { 
    if(!preg_match('/^[0-9]\d*$/',$title)){
    	$t_con[] = "title like '%".mysql_escape_string($title)."%'";
		$teams = DB::LimitQuery('team', array(
					'condition' => $t_con,
					));
		$team_ids = Utility::GetColumn($teams, 'id');
		if ( $team_ids ) {
			$condition['team_id'] = $team_ids;
		} else { $title = null; }
     }else{
        $condition['team_id'] = $title;
    }
}
if ($coupon) {
	$condition[] = "id like '%".mysql_escape_string($coupon)."%'";
}

if ($state) {
	switch(strtoupper($state)) {
		case 'Y': $condition['consume'] = 'Y'; break;
		case 'N': $condition['consume'] = 'N'; $condition[] = "expire_time >= {$daytime}"; break;
		case 'E': $condition['consume'] = 'N'; $condition[] = "expire_time < {$daytime}"; break;
	}
}

/* end filter */

$count = Table::Count('coupon', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 10);

$coupons = DB::LimitQuery('coupon', array(
	'condition' => $condition,
	'order' => 'ORDER BY team_id DESC, consume_time DESC, id ASC',
	'size' => $pagesize,
	'offset' => $offset,
));

$team_ids = Utility::GetColumn($coupons, 'team_id');
$teams = Table::Fetch('team', $team_ids);

$user_ids = Utility::GetColumn($coupons, 'user_id');
$users = Table::Fetch('user', $user_ids);

$option_state = array(
	'Y' => '已消费',
	'N' => '未消费',
	'E' => '已过期',
);

include template('biz_coupon');
