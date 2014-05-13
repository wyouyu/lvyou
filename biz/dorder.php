<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_partner();
$partner_id = abs(intval($_SESSION['partner_id']));
$login_partner = Table::Fetch('partner', $partner_id);

$condition = array(
	'partner_id' => $partner_id,
);
$condition['ptype'] = 1;
$condition['type'] =2;
$count = Table::Count('order', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 10);

$orders = DB::LimitQuery('order', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));

$ticket_ids = Utility::GetColumn($orders, 'team_id');
$tickets = Table::Fetch('ticket', $ticket_ids);

foreach($tickets as $k=>$t)
{
	$category= DB::LimitQuery('category', array(
	'condition' => array('id'=>$t['t_cate']),
	
	'one' => true,
));
	$tickets[$k]['cate']  = $category['name'];
}

$partner_ids = Utility::GetColumn($orders, 'partner_id');
$partners = Table::Fetch('ticket', $ticket_ids);
//$city_ids = Utility::GetColumn($teams, 'city_id');
//$cities = Table::Fetch('category', $city_ids);
$type = array(1=>'特价票',2=>'成人票',3=>'儿童票',4=>'套票',5=>'学生票');
include template('biz_dorder_index');
