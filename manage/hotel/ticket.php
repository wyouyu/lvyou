<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');
$id=$_GET['id'];
if ( $_POST ) {
    $table = new Table('ticket', $_POST);
	//exit(print_r($_POST));
    $table->t_type = intval($table->t_type);
    $table->t_cate = intval($table->t_cate);
    $table->t_idnum = intval($table->t_idnum);
    $table->t_status = intval($table->t_status);
    $table->t_partner = intval($table->t_partner);
    $table->book_start_time = strtotime($table->book_start_time);
    $table->book_end_time = strtotime($table->book_end_time);
    $table->end_book_time = strtotime($table->end_book_time);
    $insert = array( 't_name', 't_partner', 't_origin', 't_price', 't_type',
         't_idnum', 't_status', 't_cate','type','start_num','team_price','team_start_num','deposit','beforeday','end_book_time','book_start_time','book_end_time','ticket_place','fanxian');
    $table->SetStrip('notice');

    if($_POST['t_id'])
    {
        $p_update = array();
        $p_update['market_price'] =$_POST['t_origin'];
        $p_update['site_price'] =$_POST['t_price'];
        $p_update['team_price'] =$_POST['team_price'];
        $p_update['deposit'] = $_POST['deposit'];
        $p_update['team_deposit'] =$_POST['team_deposit'];
        
        Table::UpdateCache('ticket_price',array('ticket_id'=>$_POST['t_id'],'site_price'=>$_POST['oldprice']),$p_update);
        $table->SetPk('id',$_POST['t_id']);
        $table->update($insert);
        Session::Set('notice', '编辑成功');
    }
    else
    {
        $table->insert($insert);
        $insert_id = DB::GetInsertId();
        $ticket = Table::Fetch('ticket',$insert_id);
        $price_array['market_price'] = $ticket['t_origin'];
        $price_array['site_price'] =   $ticket['t_price'];
        $price_array['team_price'] = $ticket['team_price'];
        $price_array['deposit'] = $ticket['deposit'];
        $price_array['team_deposit'] = $ticket['team_deposit'];
        $price_array['type'] = '';
        $price_array['edit_time'] = '';
        $price_array['ticket_id'] = $insert_id;
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
        Session::Set('notice', '创建成功');
    } 
    redirect( WEB_ROOT . '/manage/hotel/ticket.php?id='.$_POST['t_partner']);
}
else
{
    if($_GET['eid'])
    {
        $cond['id'] = $_GET['eid'];
        $ticket = DB::LimitQuery('ticket', array(
            'condition' => $cond,
            'one' => true,
        ));
        //$ticket = $ticket[0];   
    }
    
    $condition['t_partner'] = $id;
    $condition['type'] = 1;
    $tickets = DB::LimitQuery('ticket', array(
        'condition' => $condition,
        'order' => 'ORDER BY id DESC',
    ));
    //print_r($tickets);
}

include template('manage_hotel_s_ticket');