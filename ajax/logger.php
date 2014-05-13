<?php 
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_manager();

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));

if($action == 'relate_data'){
	$logger = Table::Fetch('logger_admin', $id);
	$relate_data = unserialize($logger['relate_data']);
}
$html = render('manage_ajax_dialog_logger_relate_data');
json($html, 'dialog');
?>