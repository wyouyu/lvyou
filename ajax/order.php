<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();

$action = strval($_GET['action']);
$id = $order_id = abs(intval($_GET['id']));
$charge = strval($_GET['id'])=='charge';
$id = $order_id = ( $charge ? 'charge' : $id );

if (!$order_id && !$charge ) {
	json('订单记录不存在', 'alert');
}

if ( $action == 'dialog' ) {
	$html = render('ajax_dialog_order');
	json($html, 'dialog');
}
elseif($action=='showorderdetail')
{
      $order = Table::Fetch('order',$id);
      $partner = Table::Fetch('partner',$order['partner_id']);
      $ticket = Table::Fetch('ticket',$order['team_id']);
      
      $html = render('ajax_dialog_orderdetail');
      json($html, 'dialog');
}                                      
elseif ( $action == 'cardcode') {
	$cid = strval($_GET['cid']);
	$order = Table::Fetch('order', $order_id);
	if ( !$order ) json('订单记录不存在', 'alert');
	$ret = ZCard::UseCard($order, $cid);
	if ( true === $ret ) {
		json(array(
					array('data' => "代金券使用成功", 'type'=>'alert'),
					array('data' => null, 'type'=>'refresh'),
				  ), 'mix');
	}
	$error = ZCard::Explain($ret);
	json($error, 'alert');
}
elseif ( $action == 'orderdel') {
	$order = Table::Fetch('order', $order_id);
	if ( !$order ||  $order['user_id']!=$login_user_id  ) json('订单记录不存在', 'alert');
	if ( $order['state'] != 'unpay' ) {
		json('付款订单不能删除', 'alert');
	}
    /* card refund */
	if ( $order['card_id'] ) {
		Table::UpdateCache('card', $order['card_id'], array(
			'consume' => 'N',
			'team_id' => 0,
			'order_id' => 0,
		));
	}
	Table::Delete('order', $order['id']);
	Session::Set('notice', "删除订单 {$order['id']} 成功");
	json(null, 'refresh');
}
