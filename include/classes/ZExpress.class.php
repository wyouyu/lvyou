<?php
class ZExpress
{

	static public function CheckOrder($order) {
		$coupon_array = array('express');
		$team = Table::FetchForce('team', $order['team_id']);
		if (!in_array($team['delivery'], $coupon_array) || !option_yes('expressbuysms')) return;
		if ( $team['now_number'] >= $team['min_number'] ) {
			//init express sms;
			$last = ($team['conduser']=='Y') ? 1 : $order['quantity'];
			if ( $team['now_number'] - $team['min_number'] < $last) {
				$orders = DB::LimitQuery('order', array(
							'condition' => array(
								'team_id' => $order['team_id'],
								'state' => 'pay',
								),
							));
				foreach($orders AS $order) {
					sms_express_buy($order);
				}
			}
			else{
				sms_express_buy($order);
			}
		}
	}

}
