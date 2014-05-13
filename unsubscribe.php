<?php
require_once(dirname(__FILE__) . '/app.php');

$code = strval($_GET['code']);
$subscribe = Table::Fetch('subscribe', $code, 'secret');
if ($subscribe) {
	ZSubscribe::Unsubscribe($subscribe);
	Session::Set('notice', '退订成功，您不会收到每日团购信息了');
}
redirect( WEB_ROOT  . '/subscribe.php');
