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
$condition = array( 'user_id' => $login_user_id, 'team_id > 0', );
$selector = strval($_GET['s']);
$allow = array('index','unpay','pay','askrefund');

if (false==in_array($selector, $allow))  $selector == 'index';

$condition['type']=5;
$count = Table::Count('order', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 10);

$orders = DB::LimitQuery('order', array(
    'condition' => $condition,
    'order' => 'ORDER BY team_id DESC, id ASC',
    'size' => $pagesize,
    'offset' => $offset,
));
$orderss =array();
foreach($orders as $key=>$o)
{
    //print_r($login_user);
    $comment = DB::LimitQuery('comment',array('condition'=>array('partner_id'=>$o['partner_id'],'userid'=>$login_user['id']),'one'=>true));
    if(!empty($comment))
    {
        $o['comment'] =1; 
    }
    //print_r($comment);
    $orderss[] = $o;
}
//print_r($orderss);
$partner_ids =Utility::GetColumn($orders, 'partner_id');
$partners = Table::Fetch('partner', $partner_ids);


$team_ids = Utility::GetColumn($orders, 'team_id');

$tickets = Table::Fetch('ticket', $team_ids);
foreach($teams AS $tid=>$one){
    team_state($one);
    $teams[$tid] = $one;
}



$pagetitle = '国内自助游订单';
$current = 'guoneiyou';

include template('guoneiyouorder_index');
