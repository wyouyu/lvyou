<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));

if ( 'importemail' == $action ) {
	$html = render('manage_ajax_dialog_importemail');
	json($html, 'dialog');
}
