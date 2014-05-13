<?php
class ZSubscribe
{
	static public function Create($email, $city_id) 
	{
		if (!Utility::ValidEmail($email, true)) return;
		$secret = md5($email . $city_id);
		$table = new Table('subscribe', array(
					'email' => $email,
					'city_id' => $city_id,
					'secret' => $secret,
					));
		Table::Delete('subscribe', $email, 'email');
		return $table->insert(array('email', 'city_id', 'secret'));
	}

	static public function Unsubscribe($subscribe) {
		Table::Delete('subscribe', $subscribe['email'], 'email');
	}
}
