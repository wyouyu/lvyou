<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');

if (is_post()) {
    $order_id = abs(intval($_POST['order_id']));
} else {
    $order_id = $id = abs(intval($_GET['id']));
	//exit(print_r($order_id));
}

if(!$order_id || !($order = Table::Fetch('order', $order_id))) {
    redirect( WEB_ROOT. '/index.php');
}

$ticket = Table::Fetch('team', $order['team_id']);

if (is_post() && $_POST['paytype'] ) {
    $uarray = array( 'service' => pay_getservice($_POST['paytype']) );
    Table::UpdateCache('order', $order_id, $uarray);
    $order = Table::Fetch('order', $order_id);
    $order['service'] = pay_getservice($_POST['paytype']);
}


if ( $order['state'] == 'pay' ) {  
    if ( is_get() ) {
        $ticket = Table::Fetch('partner',$order['team_id']);
        $partner = Table::Fetch('partner',$order['partner_id']);
        $piaotype = array(
        '特价票','成人票','儿童票','套票','学生票'
        );
        $type = $piaotype[$ticket['t_cate']+1];
        die(include template('paysuccess'));        
    } else {
        redirect(WEB_ROOT  . "/order/gopay.php?id={$order_id}");
    }
}


if ( $_POST['action'] == 'redirect' ) {
	//exit(print_r($_POST['reqUrl']));
    redirect($_POST['reqUrl']);
}
$order = Table::FetchForce('order', $order_id);
$pay_callback = "pay_team_{$order['service']}";//pay_team_alipay();
if ( function_exists($pay_callback) ) {
    $payhtml = $pay_callback($order['money'], $order);
	//exit(print_r($order));
    die(include template('order_pay'));
}
else if ( $order['service'] == 'credit' ) {
    $total_money = $order['origin'];
    die(include template('order_pay'));
} 
else {
    showmessage('无合适的支付方式');
}
?>
