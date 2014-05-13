<?php
function rewrite_hook($c){
	$c = rewrite_city($c);
	$c = rewrite_team($c);
	$c = rewrite_partner($c);
	return $c;
}

function rewrite_city($c) {
	if (!option_yes('rewritecity')) return $c;
	if (preg_match('#/manage/#i', $_SERVER['REQUEST_URI'])) return $c;
	global $city, $INI;
	$pre = strval($INI['webroot']);
	$c = preg_replace('#city\.php\?ename=(\w+)#i', "$1", $c);
	if ($city['ename']) {
		//index
		$p = "#\"{$pre}/index.php\"#";
		$r = "{$pre}/{$city['ename']}";
		$c = preg_replace($p, $r, $c);
		//deals
		$p = "#{$pre}/team/index.php#";
		$r = "{$pre}/{$city['ename']}/deals";
		$c = preg_replace($p, $r, $c);
		//seconds
		$p = "#{$pre}/team/seconds.php#";
		$r = "{$pre}/{$city['ename']}/seconds";
		$c = preg_replace($p, $r, $c);
		//goods
		$p = "#{$pre}/team/goods.php#";
		$r = "{$pre}/{$city['ename']}/goods";
		$c = preg_replace($p, $r, $c);
		//goods
		$p = "#{$pre}/team/goods.php#";
		$r = "{$pre}/{$city['ename']}/goods";
		$c = preg_replace($p, $r, $c);
		//partners
		$p = "#{$pre}/partner/index.php#";
		$r = "{$pre}/{$city['ename']}/partners";
		$c = preg_replace($p, $r, $c);
	}
	return $c; 
}

function rewrite_team($c) {
	if (!option_yes('rewriteteam')) return $c;
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/team\.php\?id=(\d+)\"#i";
	$r = "{$pre}/team/$1.html";
	return preg_replace($p, $r, $c); 
}

function rewrite_partner($c) {
	if (!option_yes('rewritepartner')) return $c;
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/partner\.php\?id=(\d+)\"#i";
	$r = "{$pre}/partner/$1.html";
	return preg_replace($p, $r, $c); 
}
