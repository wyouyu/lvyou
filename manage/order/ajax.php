<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));

if ( 'smsexpress' == $action ) {
	sms_express($id, $flag);
	if ( true===$flag) {
		json('发送快递单号短信成功', 'alert');
	}
	json($flag, 'alert');
	json('发送单号失败', 'alert');
}
