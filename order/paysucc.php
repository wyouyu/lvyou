<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
$order_id = $_GET['id'];
$types = $_GET['type'];
if(!$order_id)
{
    showmessage('订单重复提交或找不到该订单号！','/index.php');
}
$order = Table::Fetch('order',$order_id);
$user = Table::Fetch('user', $order['user_id']);
//$partner = Table::Fetch('ticket', $order['partner_id']);
//$order = Table::FetchForce('order', $order['id']);
$ticket = Table::Fetch('ticket',$order['team_id']);
$partner = Table::Fetch('partner',$order['partner_id']);
$piaotype = array(
'特价票','成人票','儿童票','套票','学生票'
);
mail_order($order,$user,$partner);
$type = $piaotype[$ticket['t_cate']-1];
if($types==2)
{
    include template('paysuccess_fangjian');
}
else
include template('paysuccess');
  
?>
