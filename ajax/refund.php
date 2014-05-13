<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();

$action = strval($_GET['action']);
$order_id = abs(intval($_GET['id']));

if ( 'askrefund' == $action) {	
	if ($order_id) {
		$order = Table::Fetch('order', $order_id);
		$team = Table::Fetch('team', $order['team_id']);
		if (!$order||$order['rstate']!='normal'||$order['state']!='pay'){ 
		json('无法申请退款', 'alert');
		}
	}else
	    {
	    json('请选择退款订单', 'alert');
	}
	$html = render('ajax_dialog_refund');
	json($html, 'dialog');
    
}
elseif ( 'refundreason' == $action ) {
	$o_id = abs(intval($_GET['oid']));
	$r_reason = strval($_GET['n']);
	$u = array(
		'rstate' => 'askrefund',
		'rereason' => $r_reason,
		'retime' => time(),
	);
	Table::UpdateCache('order', $o_id, $u);
    json( array(
		        array('data'=>'申请退款成功,请等待管理员审核', 'type' => 'alert',),
			    array('data'=>'X.boxClose();', 'type' => 'eval',),
			    array('data'=>'null', 'type' => 'refresh',),
			   ), 'mix');
}
