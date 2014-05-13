<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('order');


$condition = array(
    'team_id > 0',
);
/* filter */
$uemail = strval($_GET['uemail']);
if ($uemail) {
    $field = strpos($uemail, '@') ? 'email' : 'username';
    //$field = is_numeric($uemail) ? 'id' : $field;
    $uuser = Table::Fetch('user', $uemail, $field);
    if($uuser) $condition['user_id'] = $uuser['id'];
    else $uemail = null;
}
$realname = $_GET['realname'];
if($realname) $condition['realname'] = $realname;

$mobile = $_GET['mobile'];
if($mobile) $condition['mobile'] = $mobile;


$id = $_GET['id']; if ($id) $condition['pay_id'] = $id;



$cbday = strval($_GET['cbday']);
$ceday = strval($_GET['ceday']);

if ($cbday) { 
    $cbtime = strtotime($cbday);
    $condition[] = "outtime = '{$cbtime}'";
}
if ($ceday) { 
    $cetime = strtotime($ceday);
    $condition[] = "lidiandate = '{$cetime}'";
}

/* end fiter */
$status = intval($_GET['status']);
if($status!=4)
{
    $condition[] = 'status !=2';
}
if($status==1)
{
    $condition['state'] = 'unpay';
}
elseif($status==2)
{
    $condition['state'] = 'pay';
}
elseif($status==3)
{
    $condition['state'] = 'pay';
}
elseif($status==4)
{
    $condition['status'] = 2;
}
//print_r($condition);
$count = Table::Count('order', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);
$condition['type']=2;
$condition['s_type']=2;
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

include template('manage_order_dujiacunticket');
