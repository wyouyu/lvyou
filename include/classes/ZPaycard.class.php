<?php
class ZPaycard
{

	static public function UsePayCard($paycard) 
	{
		global $login_user_id;
		if ( !$paycard['consume']=='N' ) return false;
		$u = array(
				'user_id' => $login_user_id,
				'recharge_time' => time(),
				'consume' => 'Y',
			  );
		Table::UpdateCache('paycard', $paycard['id'], $u);
		Table::UpdateCache('user', $login_user_id, array(
					'money' => array( "money + {$paycard['value']}" ),
					));             
		$l = array(
				'user_id' => $login_user_id,
				'admin_id' => 0,
				'money' => $paycard['value'],
				'direction' => 'income',
				'action' => 'cardstore',
				'detail_id' => $paycard['id'],
				'create_time' => time(),
			  );
		DB::Insert('flow', $l);
		return true;
	}

	static public function PayCardCreate($query) 
	{
		$need = $query['quantity'];
		while(true) {
			$id = Utility::GenSecret(16, Utility::CHAR_NUM);
			$paycard = array(
					'id' => $id,
					'value' => $query['money'],
					'consume' => 'N',
					'expire_time' => $query['expire_time'],
					);
			$need -= (DB::Insert('paycard', $paycard)) ? 1 : 0;
			if ( $need <= 0 ) return true;
		}

		return true;
	}
}
