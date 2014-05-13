<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');
$tid = $_GET['tid'];
$ticket = Table::Fetch('ticket',$tid);
$time_price = DB::LimitQuery('ticket_price',array('condition'=>array('ticket_id'=>$ticket['id']),'order'=>'order by id'));
include template('manage_ticket_price');
?>
