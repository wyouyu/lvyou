<?php
class ZCard
{
	const ERR_NOCARD = -1;
	const ERR_TEAM = -2;
	const ERR_CREDIT = -3;
	const ERR_EXPIRE = -4;
	const ERR_USED = -5;
	const ERR_ORDER = -6;

	static public function Explain($errno) {
		switch($errno) {
			case self::ERR_NOCARD : return '代金券不存在';
			case self::ERR_TEAM : return '代金券不可用于本单';
			case self::ERR_CREDIT : return '代金券面额受限';
			case self::ERR_EXPIRE : return '不在有效期内';
			case self::ERR_USED : return '代金券已被使用';
			case self::ERR_ORDER: return '每单只能用一张代金券';
		}
		return '未知错误';
	}
	
	static public function UseCard($order, $card_id) 
	{
		if ($order['card_id']) return self::ERR_ORDER;
		$card = Table::Fetch('card', $card_id);
		if (!$card) return self::ERR_NOCARD;
		if ($card['consume'] == 'Y') return self::ERR_USED;
		$today = strtotime(date('Y-m-d'));
		if ($card['begin_time'] > $today 
			|| $card['end_time'] < $today ) return self::ERR_EXPIRE;
		
		$team = Table::Fetch('team', $order['team_id']);
		if ( $card['partner_id'] > 0 
			&& $card['partner_id'] != $team['partner_id'] ) {
			return self::ERR_TEAM;
		}
		// changed by seek @2010-11-25
		// if ( $team['card'] < $card['credit'] ) return self::ERR_CREDIT;
		$finalcard = ($card['credit'] > $order['origin']) 
			? $order['origin'] : $card['credit'];
		$finalcard = ($finalcard > $team['card'])
			? $team['card'] : $finalcard;
	
		Table::UpdateCache('order', $order['id'], array(
			'card_id' => $card_id,
			'card' => $finalcard,
			'origin' => array( "origin - {$finalcard}" ),
		));
		Table::UpdateCache('card', $card_id, array(
			'consume' => 'Y',
			'team_id' => $team['id'],
			'order_id' => $order['id'],
			'ip' => Utility::GetRemoteIp(),
		));
		return true;
	}

	static public function CardCreate($query) 
	{
		$need = $query['quantity'];
		while(true) {
			$id = Utility::GenSecret(16, Utility::CHAR_NUM);
			$card = array(
					'id' => $id,
					'code' => $query['code'],
					'partner_id' => $query['partner_id'],
					'credit' => $query['money'],
					'consume' => 'N',
					'begin_time' => $query['begin_time'],
					'end_time' => $query['end_time'],
					);
			$need -= (DB::Insert('card', $card)) ? 1 : 0;
			if ( $need <= 0 ) return true;
		}

		return true;
	}
}
