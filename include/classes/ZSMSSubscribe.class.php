<?php
class ZSMSSubscribe
{
	static public function Create($mobile, $city_id, $secret=null, $enable=false) 
	{
		if (!Utility::IsMobile($mobile, true)) return;
		$secret = $secret ? $secret : Utility::VerifyCode();
		$have = Table::Fetch('smssubscribe', $mobile, 'mobile');
		if ( $have 
				&& $have['city_id'] == $city_id
				&& 'Y'==($have['enable'])
		   ) {
			return true;
		}
		$table = new Table('smssubscribe', array(
					'mobile' => $mobile,
					'enable' => $enable ? 'Y' : 'N',
					'city_id' => $city_id,
					'secret' => $secret,
					));
		Table::Delete('smssubscribe', $mobile, 'mobile');
		return $table->insert(array('mobile', 'city_id', 'secret', 'enable'));
	}

	static public function Enable($mobile, $enable=false) {
		$sms = Table::Fetch('smssubscribe', $mobile, 'mobile');
		if ( $sms ) {
			Table::UpdateCache('smssubscribe', $sms['id'], array(
				'enable' => $enable ? 'Y' : 'N',
			));
		} 
	}

	static public function Secret($mobile, $secret=null) {
		$secret = $secret ? $secret : Utility::VerifyCode();
		$sms = Table::Fetch('smssubscribe', $mobile, 'mobile');
		if ( $sms ) {
			Table::UpdateCache('smssubscribe', $sms['id'], array(
				'secret' => $secret,
			));
		}
		return $secret;
	}

	static public function UnSubscribe($mobile) {
		Table::Delete('smssubscribe', $mobile, 'mobile');
	}
}
