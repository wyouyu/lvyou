<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('order');

if($_GET['action']=='fanxian')
{
	$oid =intval($_GET['oid']);
	$money = $_GET['money'];
	$order = Table::Fetch('order',$oid);
	$user = Table::Fetch('user', $order['user_id']);
	Table::UpdateCache('user', $order['user_id'], array(
					'money' => array( "money + {$money}" ),
					));
	Table::UpdateCache('order', $order['id'], array(
					'is_fanxian' => 1,
					));
	showmessage('返现成功！','/manage/order/fanxian.php');
}

$realname = $_GET['realname'];
if($realname) $condition['realname'] = $realname;

$mobile = $_GET['mobile'];
if($mobile) $condition['mobile'] = $mobile;


$id =$_GET['id']; if ($id) $condition['smsid'] = $id;


$condition['is_feed'] = 1;
$condition[] = ' shidao>0';
$condition[] = ' user_id<>99999';
$condition[] = ' status<>2';
$condition[] = ' team_id in (select id from ticket where fanxian>0)';
$count = Table::Count('order', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);
$orders = DB::LimitQuery('order', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));

$pay_ids = Utility::GetColumn($orders, 'pay_id');
$pays = Table::Fetch('pay', $pay_ids);

$user_ids = Utility::GetColumn($orders, 'user_id');
$users = Table::Fetch('user', $user_ids);
$order_ids = Utility::GetColumn($orders, 'id');
//$order_ids = Utility::GetColumn($orders, 'id');
//$coupons = Table::Fetch('coupon', $order_ids);
//print_r($order_ids);
$team_ids = Utility::GetColumn($orders, 'partner_id');
$partner = Table::Fetch('partner', $team_ids);

$ticket_ids = Utility::GetColumn($orders, 'team_id');
$tickets = Table::Fetch('ticket', $ticket_ids);


$current ='fanxian';
include template('manage_order_fanxian');
