<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');
$id=$_GET['id'];
$sql = "DELETE FROM `ticket_price` WHERE `ticket_id` =".$id;
DB::Query($sql);
$ticket = Table::Fetch('ticket',$id);
        $price_array['market_price'] = $ticket['t_origin'];
        $price_array['site_price'] =   $ticket['t_price'];
        $price_array['team_price'] = $ticket['team_price'];
        $price_array['deposit'] = $ticket['deposit'];
        $price_array['team_deposit'] = $ticket['team_deposit'];
        $price_array['type'] = '';
        $price_array['edit_time'] = '';
        $price_array['ticket_id'] = $id;
        $daysarray = array('星期天','星期一','星期二','星期三','星期四','星期五','星期六');
        $p_insert = array('market_price','site_price','team_price','deposit','team_deposit','type','edit_time','ticket_id','tday','tdate');
        $price =new Table('ticket_price',$price_array);
        for($i=0;$i<90;$i++)
        {
            $price->tdate = date('Y-m-d',strtotime("{$i} days"));
            $day_num = date('w',strtotime($price->tdate));
            $price->tday = $daysarray[$day_num];
            $price->insert($p_insert);
        }
        Session::Set('notice', '更新成功');
		redirect( WEB_ROOT . '/manage/ticket/ticket.php?id='.$_GET['tpid']);
?>