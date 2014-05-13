<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');
$id = intval($_GET['id']);
$order = Table::Fetch('order', $id);

if (!($pay_id = $order['pay_id'])) {
    $randid = strtolower(Utility::GenSecret(4, Utility::CHAR_WORD));
    $pay_id = "go-{$order['id']}-{$order['quantity']}-{$randid}";
    //exit(print_r($pay_id));
    Table::UpdateCache('order', $order['id'], array(
                'pay_id' => $pay_id,
                ));
}
$order = Table::FetchForce('order', $order['id']);
if($order['type']==1||$order['type']==2)
{
   $ticket = Table::Fetch('ticket', $order['team_id']); 
}
elseif($order['type']==4)
{
    $team = Table::Fetch('team', $order['team_id']);
}

$ticketType = getTicketType($ticket['t_cate']);
if($order['type']==2)
die(include template('ticketCheck'));
else
die(include template('ticketCheck2'));
?>
