<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');
$content = file_get_contents($_FILES['upload_txt']['tmp_name']);
$emails = preg_split('/([,;\s\r\n])+/', $content, -1, PREG_SPLIT_NO_EMPTY);
$city_id = abs(intval($_POST['city_id']));

$create = 0;
foreach($emails AS $one) {
	$create += (ZSubscribe::Create($one, $city_id)) ? 1 : 0;
}
$cityname = $allcities[$city_id];
if ( $create ) {
	Session::Set('notice', "导入{$create}封订阅邮件成功！");
} else {
	Session::Set('error', "导入0封订阅邮件");
}
redirect(null);
