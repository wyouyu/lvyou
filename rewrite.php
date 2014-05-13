<?php
require_once(dirname(__FILE__) . '/app.php');
$uri = strval($_SERVER['REQUEST_URI']);
$pre = strval($_INI['webroot']);
if($pre&&0===strpos($uri, $pre)) $uri = substr($uri, 0+strlen($pre));
$u = parse_url($uri); $uri = $u['path'];

/* city */
if(preg_match("#^/(\w+)$#i", $uri, $m)) {
	$_GET['ename'] = $m[1]; 
	die(require_once(dirname(__FILE__) . '/city.php'));
}

/* team */
if(preg_match('#^/(team|partner)/(\d+).html$#i', $uri, $m)) {
	$_GET['id'] = abs(intval($m[2]));
	$type = strtolower(strval($m[1]));
	die(require_once(dirname(__FILE__) . "/{$type}.php"));
}

/* list */
if(preg_match('#^/(\w+)/(deals|seconds|goods|partners)$#i', $uri, $m)) {
	$ename = strval($m[1]); $city = ename_city($ename);
	$type = strtolower(strval($m[2]));
	switch($type) {
		case 'partners':
			die(require_once(dirname(__FILE__) . "/partner/index.php"));
		case 'deals':
			die(require_once(dirname(__FILE__) . "/team/index.php"));
		default:
			die(require_once(dirname(__FILE__) . "/team/{$type}.php"));
	}
}
