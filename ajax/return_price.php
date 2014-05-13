<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
$date = $_POST['outtime'];
$ticket_id = $_POST['ticketid'];
$num = $_POST['num'];
$ticket = Table::Fetch('ticket',$ticket_id);
$price = DB::LimitQuery('ticket_price',array('condition'=>array('tdate'=>$date,'ticket_id'=>$ticket_id),'one'=>true));
if($ticket['team_start_num'])
{
    if($num>=$ticket['team_start_num'])
    {
    $data['price'] = $ticket['team_price'];
    die(json_encode($data));
    }
}
if(!empty($price))
{
    $data['price'] = intval($price['site_price'])-intval($ticket['fanxian']);
}
else
{
    $data['price'] = intval($ticket['t_price'])-intval($ticket['fanxian']);
}
die(json_encode($data));
?>
