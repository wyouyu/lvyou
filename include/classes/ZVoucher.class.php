<?php
class ZVoucher
{
	static function Create($code, $team_id) {
		DB::Insert('voucher', array(
					'code' => $code,
					'team_id' => $team_id,
					));
	}

	static public function CheckOrder($order) {
		$voucher_array = array('voucher');
		$team = Table::FetchForce('team', $order['team_id']);
		if (!in_array($team['delivery'], $voucher_array)) return;
		if ( $team['now_number'] >= $team['min_number'] ) {
			//init voucher create;
			$last = ($team['conduser']=='Y') ? 1 : $order['quantity'];
			$offset = max(5, $last);
			if ( $team['now_number'] - $team['min_number'] < $last) {
				$orders = DB::LimitQuery('order', array(
							'condition' => array(
								'team_id' => $order['team_id'],
								'state' => 'pay',
								),
							));
				foreach($orders AS $order) {
					self::Assign($order);
				}
			}
			else{
				self::Assign($order);
			}
		}
	}

	static function Assign($order) {
		$ccon = array('order_id' => $order['id']);
		$count = Table::Count('voucher', $ccon);
		while($count<$order['quantity']) {
			$voucher = self::GetValidVoucher($order['team_id']);
			if (!$voucher) break;
			Table::UpdateCache('voucher', $voucher['id'], array(
						'user_id' => $order['user_id'],
						'order_id' => $order['id'],
						));
			$count = Table::Count('voucher', $ccon);
		}
	}

	static function GetValidVoucher($team_id) {
		$ccon = array('team_id' => $team_id, 'order_id' => 0);
		return DB::LimitQuery('voucher', array(
					'condition' => $ccon,
					'one' => true,
					));
	}
}
