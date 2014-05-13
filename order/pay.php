<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');

need_login();

if (is_post()) {
	$order_id = abs(intval($_POST['order_id']));
} else {
	if ( $_GET['id'] == 'charge' ) {
		redirect( WEB_ROOT. '/credit/index.php');
	}
	$order_id = $id = abs(intval($_GET['id']));
}
if(!$order_id || !($order = Table::Fetch('order', $order_id))) {
	redirect( WEB_ROOT. '/index.php');
}
if ( $order['user_id'] != $login_user['id']) {
	redirect( WEB_ROOT . "/team.php?id={$order['team_id']}");
}

$team = Table::Fetch('team', $order['team_id']);
team_state($team);

if (is_post() && $_POST['paytype'] ) {
	$uarray = array( 'service' => pay_getservice($_POST['paytype']) );
	Table::UpdateCache('order', $order_id, $uarray);
	$order = Table::Fetch('order', $order_id);
	$order['service'] = pay_getservice($_POST['paytype']);
}

if ( $_POST['paytype']!='credit' 
		&& $_POST['service']!='credit' 
		&& $team['team_type']=='seconds' 
		&& ($order['origin']>$login_user['money'])
		&& option_yes('creditseconds')
   ) {
	$need_money = ceil($order['origin'] - $login_user['money']);
	Session::Set('error', "秒杀项目仅可以使用余额付款，您的余额不足，还需要充值{$need_money}元才可以完成秒杀");
	redirect(WEB_ROOT . "/credit/charge.php?money={$need_money}");
}

//peruser buy count
if ($_POST && $team['per_number']>0) {
	$now_count = Table::Count('order', array(
		'user_id' => $login_user_id,
		'team_id' => $team['id'],
		'state' => 'pay',
	), 'quantity');
	$leftnum = ($team['per_number'] - $now_count);
	if ($leftnum <= 0) {
		Session::Set('error', '您购买本单产品的数量已经达到上限，快去关注一下其他产品吧！');
		redirect( WEB_ROOT . "/team.php?id={$id}"); 
	}
}

//payed order
if ( $order['state'] == 'pay' ) {  
	if ( is_get() ) {
          
		die(include template('order_pay_success'));		
	} else {
		redirect(WEB_ROOT  . "/order/pay.php?id={$order_id}");
	}
}

$total_money = moneyit($order['origin'] - $login_user['money']);
if ($total_money<0) { 
	$total_money = 0; $order['service'] = 'credit'; 
} else if($_POST){
	$credit = moneyit($order['origin'] - $total_money);
	if ($order['credit']!=$credit) {
		Table::UpdateCache('order', $order_id, array('credit'=>$credit,));
	}
}

/* generate unique pay_id */
if (!($pay_id = $order['pay_id'])) {
	$randid = strtolower(Utility::GenSecret(4, Utility::CHAR_WORD));
	$pay_id = "go-{$order['id']}-{$order['quantity']}-{$randid}";
	Table::UpdateCache('order', $order['id'], array(
				'pay_id' => $pay_id,
				));
}


/* noneed pay where goods soldout or end */
if ($team['close_time']) {
	Session::Set('notice', '本单产品团购已结束，现在不能进行付款');
	redirect(WEB_ROOT  . "/team.php?id={$order['team_id']}");
}
/* noneed pay where goods soldout or end */
if ($team['now_number']>$team['maxnumber'] && $team['maxnumber']>0 ) {
	Session::Set('notice', '本单产品团购已卖光，现在不能进行付款');
	redirect(WEB_ROOT  . "/team.php?id={$order['team_id']}");
}
/* end */

/* credit pay */
if ( $_POST['action'] == 'redirect' ) {
	redirect($_POST['reqUrl']);
}
elseif ( $_POST['service'] == 'credit' ) {
	if ( $order['origin'] > $login_user['money'] ){
		Table::Delete('order', $order_id);
		redirect( WEB_ROOT . '/order/index.php');
	}
	Table::UpdateCache('order', $order_id, array(
				'service' => 'credit',
				'money' => 0,
				'state' => 'pay',
				'credit' => $order['origin'],
				'pay_time' => time(),
				));
	$order = Table::FetchForce('order', $order_id);
	ZTeam::BuyOne($order);
	redirect( WEB_ROOT . "/order/pay.php?id={$order_id}");
}

$pay_callback = "pay_team_{$order['service']}";
if ( function_exists($pay_callback) ) {
	$payhtml = $pay_callback($total_money, $order);
	die(include template('order_pay'));
}
else if ( $order['service'] == 'credit' ) {
	$total_money = $order['origin'];
	die(include template('order_pay'));
} 
else {
	Session::Set('error', '无合适的支付方式或余额不足');
	redirect( WEB_ROOT. "/team.php?id={$order_id}");
}
