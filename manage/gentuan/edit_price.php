<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');
if(!$_POST['submit'])
{
    $tid = $_GET['tid'];
    $ticket = Table::Fetch('ticket',$tid);
    include template('manage_edit_price');
}
else
{
    $insert = array(
        'market_price'=>$_POST['market_price'],
        'site_price'=>$_POST['site_price'],
        'team_price'=>$_POST['team_price'],
        'deposit'=>$_POST['deposit'],
        'team_deposit'=>$_POST['team_deposit']);
    $cbxWeek = $_POST['cbxWeek'];
    $days = array();
    foreach($cbxWeek as $weeks)
    {
        $k = '"'.$weeks.'"';
        $days[] = $k;
    }
    $week_str = implode(',',$days);
    $week_str = rtrim($week_str,',');
    $condition = array('tday in ('.$week_str.')','ticket_id'=>$_POST['tid']);
    Table::UpdateCache('ticket_price',$condition,$insert);
    showmessage('更新成功','/manage/ticket/price.php?tid='.$_POST['tid']);
}

?>
