<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');
$id = intval($_GET['id']);
$order = Table::Fetch('order', $id);
if (!$order) { 
	Session::Set('error', '订单不存在');
	redirect( WEB_ROOT . '/index.php' );
}
if ( $order['user_id'] != $login_user['id']) {
	redirect( WEB_ROOT . "/team.php?id={$order['team_id']}");
}
$team = Table::Fetch('team', $order['team_id']);
$team['state'] = team_state($team);
if ( $team['close_time'] ) {
	redirect( WEB_ROOT . "/team.php?id={$id}");
}

if ( $order['state'] == 'unpay' ) {

	/* payservice choice */
	if(@$INI[$order['service']]['mid'] || @$INI[$order['service']]['acc']) {
		$ordercheck[$order['service']] = 'checked';
	}
	else {
		foreach($payservice AS $pone) {
			if(@$INI[$order['service']]['mid'] || @$INI[$order['service']]['acc']) { $ordercheck[$order['service']] = 'checked'; }
		}
	}
	$credityes = ($login_user['money'] >= $order['origin']);
	$creditonly = ($team['team_type'] == 'seconds' && option_yes('creditseconds'));

	/* generator unique pay_id */
	if (! ($order['pay_id'] 
				&& (preg_match('#-(\d+)-(\d+)-#', $order['pay_id'], $m) 
					&& ( $m[1] == $order['id'] && $m[2] == $order['quantity']) )
		  )) {
		$randid = strtolower(Utility::GenSecret(4, Utility::CHAR_WORD));
		$pay_id = "go-{$order['id']}-{$order['quantity']}-{$randid}";
		Table::UpdateCache('order', $order['id'], array(
					'pay_id' => $pay_id,
					));
	}
	die(include template('order_check'));
}
redirect( WEB_ROOT . "/order/view.php?id={$id}");
