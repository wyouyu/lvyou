<?php
function configure_keys() {
	return array(
		//system
		'db',
		'memcache',
		'webroot',
		'system',
		'bulletin',
		//pay
		'alipay',
		'tenpay',
        'sdopay',
		'bill',
		'chinabank',
		'paypal',
		'yeepay',
        'cmpay',
        'gopay',
		'other',
		//settings
		'option',
		'mail',
		'sms',
		'credit',
		'skin',
		'authorization',
        //login
		'sina',
		'qq',
		'qzone',
		'slide'
	);
}
function configure_save($key=null) {
	global $INI;
	if ($key && isset($INI[$key])) {
		return _configure_save($key, $INI[$key]);
	}
	$keys = configure_keys();
	foreach($keys AS $one) {
		if(isset($INI[$one])) _configure_save($one, $INI[$one]);
	}
	return true;
}

function _configure_save($key, $value) {
	if (!key) return;
	$php = DIR_CONFIGURE . '/' . $key . '.php';
	$v = "<?php\r\n\$value = ";
	$v .= var_export($value, true);
	$v .=";\r\n?>";
	return file_put_contents($php, $v);
}

function configure_load() {
	global $INI;
	$keys = configure_keys();
	foreach($keys AS $one) {
		$INI[$one] = _configure_load($one);
	}
	return $INI;
}

function _configure_load($key=null) {
	if (!$key) return NULL;
	$php = DIR_CONFIGURE . '/' . $key . '.php';
	if ( file_exists($php) ) {
		require_once($php);
	}
	return $value;
}
