<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
$date = $_POST['outtime'];
$lidiandate = $_POST['lidiandate'];
$d = strtotime($date);
$l = strtotime($lidiandate);
$p = $l-$d;

$ticket_id = $_POST['ticketid'];
$num = $_POST['num'];
$ticket = Table::Fetch('ticket',$ticket_id);
//print_r($ticket);
//exit();
if($ticket['team_start_num'])
{
    if($num>=$ticket['team_start_num'])
    {
        $data['price'] = $num*$ticket['team_price'];
        die(json_encode($data));
    }
}
$days = $p/(3600*24);

if($days>=1)
{
   $data =array();
   for($i=1;$i<=$days;$i++)
   {
        $date_u = ($i-1)*(3600*24)+$d;
        $date = date('Y-m-d',$date_u);
        //echo $date;
        //exit();
        $price = DB::LimitQuery('ticket_price',array('condition'=>array('tdate'=>$date,'ticket_id'=>$ticket_id),'one'=>true));
        if(!empty($price))
        {
            $data['price'] += intval($price['site_price'])-intval($ticket['fanxian']);
        }
        else
        {
            $data['price'] += intval($ticket['t_price'])-intval($ticket['fanxian']);
        }
   }
   $data['price'] = $num*$data['price'];
}

die(json_encode($data));
?>
