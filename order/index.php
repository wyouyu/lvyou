<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
if($_GET['action']=='chanel')
{
    $order = Table::Fetch('order',$_GET['cid']);
     Table::UpdateCache('order', $order['id'], array(
                    'status' => 2,
                   // 'sms_time' => time(),
                    ));
     showmessage('订单取消成功','/order/index.php');
}
//取票反馈 
if($_POST)
{
	$order_id =intval($_POST['order_id']);
	$choose_type = intval($_POST['get_ticket']);
	$ticket_num = intval($_POST['ticket_num']);
	$ticket_price = intval($_POST['ticket_price']);
	$ticket_arr = array('is_feed'=>1);
	//exit(print_r($_POST));
	DB::Update('order',$order_id,$ticket_arr);
	if($choose_type==1)
	{
		if($ticket_num>0)
			$ticket_arr['shidao'] = $ticket_num;
		else
			$ticket_arr['shidao'] = 0;
	}
	else
	{
		$ticket_arr['shidao'] = 0;
	}
	DB::Update('order',$order_id,$ticket_arr);
	showmessage('反馈成功，感谢您的反馈','/order/index.php');
}
$condition = array( 'user_id' => $login_user_id, 'team_id > 0', );
$selector = strval($_GET['s']);
$allow = array('index','unpay','pay','askrefund');

if (false==in_array($selector, $allow))  $selector == 'index';

$condition['type']=1;
//print_r($condition);
$count = Table::Count('order', $condition);
list($pagesize, $offset, $pagestring) = pagestring_search($count, 20);

$orders = DB::LimitQuery('order', array(
	'condition' => $condition,
	'order' => 'ORDER BY  id desc',
	'size' => $pagesize,
	'offset' => $offset,
));
$orderss =array();
foreach($orders as $key=>$o)
{
    $comment = DB::LimitQuery('comment',array('condition'=>array('partner_id'=>$o['partner_id'],'userid'=>$login_user),'one'=>true));
    if(!empty($comment))
    {
        $o['comment'] =1; 
    }
    $orderss[] = $o;
}

$partner_ids =Utility::GetColumn($orders, 'partner_id');
$partners = Table::Fetch('partner', $partner_ids);


$team_ids = Utility::GetColumn($orders, 'team_id');

$tickets = Table::Fetch('ticket', $team_ids);
foreach($teams AS $tid=>$one){
	team_state($one);
	$teams[$tid] = $one;
}



$pagetitle = '我的订单';
$current = 'index';
include template('account_index');
