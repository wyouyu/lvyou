<?php
class ZTeam
{
	static public function DeleteTeam($id) {
		$orders = Table::Fetch('order', array($id), 'team_id');
		foreach( $orders AS $one ) {
			if ($one['state']=='pay') return false;
			if ($order['card_id']) {
				Table::UpdateCache('card', $order['card_id'], array(
					'team_id' => 0,
					'order_id' => 0,
					'consume' => 'N',
				));
			}
			Table::Delete('order', $one['id']);
		}
		return Table::Delete('team', $id);
	}

	static public function BuyOne($order) {
		$order = Table::FetchForce('order', $order['id']);
		$order_id = abs(intval($order['id']));
		$team_id = abs(intval($order['team_id']));
		$team = Table::FetchForce('team', $order['team_id']);
		$plus = $team['conduser']=='Y' ? 1 : $order['quantity'];
		$team['now_number'] += $plus;

		/* close time */
		if ( $team['max_number']>0 
				&& $team['now_number'] >= $team['max_number'] ) {
			$team['close_time'] = time();
		}

		/* reach time */
		if ( $team['now_number']>=$team['min_number']
			&& $team['reach_time'] == 0 ) {
			$team['reach_time'] = time();
		}

		Table::UpdateCache('team', $team['id'], array(
			'close_time' => $team['close_time'],
			'reach_time' => $team['reach_time'],
			'now_number' => array( "`now_number` + {$plus}", ),
		));
		
		//UPDATE buy_id
		$SQL = "UPDATE `order` o,(SELECT max(buy_id)+1 AS c FROM `order` WHERE state = 'pay' and team_id = '{$team_id}') AS c SET o.buy_id = c.c, o.luky_id = 100000 + floor(rand()*100000) WHERE o.id = '{$order_id}' AND buy_id = 0;";
		DB::Query($SQL);
		/* send sms Immediately  */
		if(option_yes('buycouponsms')) sms_buy($order);
		/* cash flow */
		ZFlow::CreateFromOrder($order);
		/* order : send coupon ? */
		ZCoupon::CheckOrder($order);
		/* order : send voucher ? */
		ZVoucher::CheckOrder($order);
        /* order : send express sms ? */
		ZExpress::CheckOrder($order);
		/* order : invite buy */
		ZInvite::CheckInvite($order);
		/* credit */
		ZCredit::Buy($order['user_id'], $order);
	}
}
?>
