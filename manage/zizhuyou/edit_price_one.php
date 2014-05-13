<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');



if(!$_POST['submit'])
{
    $tid = $_GET['tid'];
    $pid = $_GET['pid'];
    $price = Table::Fetch('ticket_price',$pid);
    $ticket = Table::Fetch('ticket',$tid);
    $time_price = DB::LimitQuery('ticket_price',array('condition'=>array('ticket_id'=>$ticket['id']),'order'=>'order by id'));
    include template('manage_edit_price_one');
}
else
{
    $table = new Table('ticket_price', $_POST);
    $insert = array('market_price','site_price','team_price','deposit','team_deposit','edit_time');
    $table->edit_time = time();
    $table->SetPk('id',$_POST['pid']);    
    if($table->Update($insert))
        showmessage('更新成功','/manage/ticket/price.php?tid='.$_POST['tid']);
    else
        showmessage('更新失败','/manage/ticket/price.php?tid='.$_POST['tid']);
}
?>
