<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

if ( $_POST ) {
	$city_id = $_POST['city_id'];
	$source = $_POST['source'];
	if ( empty($city_id) ) die('-ERR ERR_NO_DATA');
	if ( empty($source) ) die('-ERR ERR_NO_DATA');

	$emails = array();

	if ( in_array('user', $source ) ) {
		$rows = DB::LimitQuery('user', array(
					'condition' => array(
						'city_id' => $city_id,
						),
					'select' => 'email',
					));
		foreach($rows As $one) {
			$emails[] = array('email'=>$one['email']);
		}
	}
	if ( in_array('subscribe', $source ) ) {
		$rows = DB::LimitQuery('subscribe', array(
					'condition' => array(
						'city_id' => $city_id,
						),
					'select' => 'email',
					));
		foreach($rows As $one) {
			$emails[] = array('email'=>$one['email']);
		}
	}

	if ( $emails ) {
		$kn = array(
				'email' => 'Email',
				);
		$name = "email_".date('Ymd');
		down_xls($emails, $kn, $name);
	}
	die('-ERR ERR_NO_DATA');
}

include template('manage_market_downemail');
