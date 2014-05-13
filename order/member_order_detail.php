<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$oderid = $_GET['id'];

$order = Table::Fetch('order',$oderid);
$partner = Table::Fetch('partner',$order['partner_id']);

$type=array(1=>'特价票',2=>'成人票',3=>'儿童票',4=>'套票',5=>'学生票');

$ticket = Table::Fetch('ticket',$order['team_id']);
include template('member_order_detail');
?>
