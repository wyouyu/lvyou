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

$partner = Table::Fetch('partner',$order['partner_id']);

include template('paysuccess2');
  
?>
