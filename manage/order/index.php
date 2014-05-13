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

$sname = $_GET['sname'];
if($sname) 
{
    $sql = "select `title`,`id` from partner where title like '%".$sname."%'";
    $result = DB::GetQueryResult($sql,false);
    $r =array();
    foreach($result as $v)
    {
        $r[] = $v['id'];
    }
    $r_str = implode(',',$r);
    $condition[] = 'partner_id in ('.$r_str.')';
}
$id =$_GET['id']; if ($id) $condition['smsid'] = $id;
$team_id = abs(intval($_GET['team_id']));
if ($team_id && in_array($team_id, $t_id)) {
	$condition['team_id'] = $team_id;
} else { $team_id = null; }

$cbday = strval($_GET['cbday']);
if ($cbday) { 
	$cbtime = strtotime($cbday);
	$condition[] = "outtime = '{$cbtime}'";
}

$obday = strval($_GET['obday']);
if($obday)
{
	$obtime = strtotime($obday);
	$condition[] = "outtime >= '{$obtime}'";
}
$oeday = strval($_GET['oeday']);
if($oeday)
{
	$oetime = strtotime($oeday);
	$condition[] = "outtime <= '{$oetime}'";
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
$condition['type'] = 1;
$condition['ptype'] = 1;
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

foreach($tickets as $k=>$t)
{
	$category= DB::LimitQuery('category', array(
	'condition' => array('id'=>$t['t_cate']),
	
	'one' => true,
));
	$tickets[$k]['cate']  = $category['name'];
}
$tday = strtotime(date('Y-m-d',time()));

$category_ids = Utility::GetColumn($orders, 'team_id');

if($_GET['action']=='del')
{
    Table::UpdateCache('order',$_GET['oid'],array(
            'status'=>'2',
    ));
    showmessage('操作成功！','/manage/order/');
}
if($_GET['action']=='chenldel')
{
    Table::UpdateCache('order',$_GET['oid'],array(
            'status'=>'1',
    ));
    showmessage('操作成功！','/manage/order/');
}
$current ='index';
include template('manage_order_index');
