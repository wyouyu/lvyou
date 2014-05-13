<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
need_login();

$id = $order_id = abs(intval($_GET['id']));
$html = render('ajax_dialog_ordercomment');
json($html, 'dialog');
?>
