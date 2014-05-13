<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$action = strval($_GET['action']);
$id = strval($_GET['id']);
$sec = strval($_GET['secret']);

if ($action == 'needlogin') {
	$html = render('ajax_dialog_needlogin');
	json($html, 'dialog');
}
else if ($action == 'authorization') {
	need_auth('super');
	$user = Table::Fetch('user', $id);
	$html = render('manage_ajax_dialog_authorization');
	json($html, 'dialog');
}
else if('locale' == $action) {
	$v = cookieget('locale', 'zh_cn');
	cookieset('locale', ($v=='zh_cn' ? 'zh_tw' : 'zh_cn'));
	json(null, 'refresh');
}
else if('googlemap' == $action) {
	$ll = strval($_GET['ll']);
	if(!$ll) $ll = '23.11,113.24';
	list($longi, $lati) = preg_split('/[,\s]+/',$ll,-1,PREG_SPLIT_NO_EMPTY);
	$html = render('manage_ajax_dialog_googlemap');
	json($html, 'dialog');
}
else if('baidumap'== $action){
	$ll = strval($_GET['ll']);	
	if(!$ll) $ll = '39.915,116.404';
	list($longi, $lati) = preg_split('/[,\s]+/',$ll,-1,PREG_SPLIT_NO_EMPTY);
	$html = render('manage_ajax_dialog_baidumap');
	json($html, 'dialog');
}
else if('showgooglemap' == $action) {
	$partner = Table::Fetch('partner', $id);
	$ll = $partner['longlat'];
	if(!$ll) $ll = '23.11,113.24';
	list($longi, $lati) = preg_split('/[,\s]+/',$ll,-1,PREG_SPLIT_NO_EMPTY);
	$html = render('ajax_dialog_googlemap');
	json($html, 'dialog');
}
else if('showbaidumap' == $action){
	$partner = Table::Fetch('partner',$id);
	$ll = $partner['longlat'];
	if(!$ll) $ll = '39.915,116.404';	
	list($longi,$lati) = preg_split('/[,\s]+/',$ll,-1,PREG_SPLIT_NO_EMPTY);
	$html = render('ajax_dialog_baidumap');
	json($html,'dialog');
}
