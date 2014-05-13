<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
need_manager();

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));
$team = Table::Fetch('team', $id);

if ( 'send' == $action ) {
	$c = array ('team_id'=>$id,'state'=>'pay');
	$orders = DB::LimitQuery('order', array(
			'condition' => $c,
		));
	if(!$orders) json('该项目没有订单',alert);
	
	foreach($orders as $k=>$v){
			$coupon = DB::LimitQuery('coupon', array(
				'condition' => array('order_id'=>$v['id'],'consume'=>'N'),
			));
			if(!$coupon) continue;
			sms_expire($v);
		}
	json( array(
				array('data'=>'发送成功', 'type' => 'alert',),
				array('data'=>'X.boxClose();', 'type' => 'eval',),
				array('data'=>'null', 'type' => 'refresh',),
			   ), 'mix');
}
