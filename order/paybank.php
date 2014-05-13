<?php
/* payservice */
$payservice = array(
	'credit', 'alipay', 'tenpay','sdopay', 'paypal', 'bill', 'chinabank', 'yeepay','cmpay','gopay',
);

/* paybank settings */
$qqbank = array(
		'cmb' => '1038',
		'icbc' => '1002',
		'ccb' => '1034',
		'abc' => '1005', 

		'comm' => '1020',
		'spdb' => '1004',
		'citic' => '1021',
		'cib' => '1009',

		'gdb' => '1027',
		'sdb' => '1008',
		'cmbc' => '1006',
		'bofc' => '1052',

		'cebb' => '1022',
		'pingan' => '1010',
		'bob' => '1032',
);
$paybank = array_keys($qqbank);

/* yeepay bank settings */
$yeepaybank = array(
		'cmb' => 'CMBCHINA-NET',
		'icbc' => 'ICBC-NET',
		'ccb' => 'CCB-NET',
		'abc' => 'ABC-NET', 

		'comm' => 'BOCO-NET',
		'spdb' => 'SPDB-NET',
		'citic' => 'ECITIC-NET',
		'cib' => 'CIB-NET',

		'gdb' => 'GDB-NET',
		'sdb' => 'SDB-NET',
		'cmbc' => 'CMBC-NET',
		'bofc' => 'BOC-NET',

		'cebb' => 'CEB-NET',
		'pingan' => 'PAB-NET',
		'bob' => 'BCCB-NET',
);


/* sdopay bank settings */
$sdopaybank = array(
		'cmb' => 'CMB-sdo',
		'icbc' => 'ICBC-sdo',
		'ccb' => 'CCB-sdo',
		'abc' => 'ABC-sdo', 

		'comm' => 'COMM-sdo',
		'spdb' => 'SPDB-sdo',
		'citic' => 'CITIC-sdo',
		'cib' => 'CIB-sdo',

		'gdb' => 'GDB-sdo',
		'sdb' => 'SDB-sdo',
		'cmbc' => 'CMBC-sdo',
		'bofc' => 'BOC-sdo',

		'cebb' => 'CEB-sdo',
		'pingan' => 'SZPAB-sdo',
		'bob' => 'BCCB-sdo',
);
/* gopay bank settings */
$gopaybank = array(
		'cmb' => '0000000002',
		'icbc' => '0000000003',
		'ccb' => '0000000001',
		'abc' => '0000000005', 

		'comm' => '0000000006',
		'spdb' => '0000000011',
		'citic' => '0000000014',
		'cib' => '0000000009',

		'gdb' => '0000000012',
		'cmbc' => '0000000007',
		'bofc' => '0000000004',

		'cebb' => '0000000015', 
);
function pay_getqqbank($paytype='cmbc') {
	global $qqbank;
	$paytype = strtolower($paytype);
	return isset($qqbank[$paytype]) ? $qqbank[$paytype] : 0;
}

function pay_getservice($paytype='tenpay') {
	global $payservice;
	$spaytype = strtolower($paytype);
	if (empty($spaytype) || in_array($spaytype, $payservice))
		return $spaytype;
	if (preg_match('/-NET$/', $paytype)) {
		$_REQUEST['pd_FrpId'] = $paytype;
		return 'yeepay';
	}
    if (preg_match('/-sdo$/', $paytype)) {
		//$_REQUEST['_bankCode'] = $paytype;
		return 'sdopay';
	}
    if (preg_match('/\d{10}$/', $paytype)) {
		return 'gopay';
	}
	return 'tenpay';
}
