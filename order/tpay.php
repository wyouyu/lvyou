<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');
$sceneryid = $_GET['sceneryid'];
$ticketid = $_GET['ticketid'];
$partner = DB::LimitQuery('partner', array(
    'condition' => 'id='.$sceneryid,
    'order' => 'ORDER BY id DESC',
    'one' => true,
));
$ticket = DB::LimitQuery('ticket', array(
    'condition' => 'id='.$ticketid,
    'one' => true,
));
$maxdate = ($ticket['book_end_time']-$ticket['book_start_time'])/(3600*24);
$beginday = ($ticket['book_start_time']-strtotime(date('Y-m-d',time())))/(3600*24);
if(!empty($_POST['outtime']))
{
    $date_str = $_POST['outtime'];
}
else
{
    $date_str = date('Y-m-d',time());
}

$price = DB::LimitQuery('ticket_price',array('condition'=>array('tdate'=>$date_str,'ticket_id'=>$ticket['id']),'one'=>true));
//exit(print_r($price));
if($price)
{
    $ticket['t_price'] = $price['site_price'];
}
else
{
    $ticket['t_price'] = $ticket['t_price'];
}
if ( is_get() ) {
		Session::Set('loginpage', $_SERVER['REQUEST_URI']);
}
if ( $_POST ) {
	
	if($ticket['fanxian']>0&&!$login_user_id)
	{
		showmessage('您购买的门票可以返现,请登录后再购买',WEB_ROOT .'/account/login.php');
	}
    $table = new Table('order', $_POST);
    if ( $table->quantity == 0 ) {
        showmessage('购买数量不能小于1份');
    }
    if($login_user_id)
    {
        $table->user_id = $login_user_id;
    }
    else
    {
        $table->user_id ='99999';//99999代表匿名购买
    }
    $table->team_id = $ticketid;
    //$table->buy_id = $sceneryid;
   
    $table->price = intval($ticket['t_price'])-intval($ticket['fanxian']);
    $table->city_id =$partner['city_id'];
    $table->outtime =strtotime($_POST['outtime']);
    $table->lidiandate =strtotime($_POST['lidiandate']);
    $table->partner_id = $sceneryid;
    $table->create_time = time();
   // if(!$_POST['lidiandate'])
   // $table->money = $table->quantity * $ticket['t_price'];
    
    $table->origin = $ticket['t_origin'];
    $insert = array(
            'user_id', 'team_id', 'city_id', 'state','pay_id', 
            'origin', 'price','money','tianshu',
             'realname', 'mobile', 'partner_id','ptype',
            'quantity', 'create_time','outtime','type','lidiandate','s_type','fadan','smsid'
        );
        
    $pay_type = $_POST['paytype'];
     
    if($pay_type==2)
    {
        $table->state = 'unpay';
        $table->ptype = 2;
        $table->fadan = 2;
        $table->smsid = 'W'.seed();
        if ($flag = $table->Insert($insert)) {
            $order_id = DB::GetInsertId();
        }
        redirect(WEB_ROOT."/order/tcheck.php?id={$order_id}");
    }
    else
    {
        
        $table->state = 'pay';
        $table->pay_id = 'ticket';
        $table->fadan = 2;
        $table->ptype = 1;
        $table->smsid = 'W'.seed();
        if ($flag = $table->Insert($insert)) {
			
            $order_id = DB::GetInsertId();
            $randid = strtolower(Utility::GenSecret(4, Utility::CHAR_WORD));
            $pay_id = "go-{$order_id}-{$table->quantity}-{$randid}";
            Table::UpdateCache('order', $order_id, array(
                'pay_id' => $pay_id,
                ));
			
            $order = Table::Fetch('order', $order_id);
            if($ticket['type']==1)//预订房间 直接发短信
            {
                 Table::UpdateCache('order', $order_id, array(
                    'shenhe' => 2,//标记为审核成功
                 ));
				 //exit(print_r(1234));
                 ZCoupon::TicketCreate($order);//发送短信验证码
				 //mail_order($order);
            }
            else
            {
                 Table::UpdateCache('order', $order_id, array(
                    'shenhe' => 1,//标记为待审核阶段
                 ));
				 //mail_order($order);
            }
			
           
        }
		//exit('123');
        if($_POST['ntype']==2)
        {
           // Table::UpdateCache('order', $order_id, array(
              //  's_type' => 1,
               // ));
			  ZCoupon::TicketCreate($order,'',$shanghu=1);//给商户发送短信
            redirect("/order/paysucc.php?id=".$order_id.'&type=2');
        }
        else
        {
           // if($_POST['ntype']==1)
            //{
               // Table::UpdateCache('order', $order_id, array(
               // 's_type' => 2,
               // ));
           // }
            redirect("/order/paysucc.php?id=".$order_id);
        }
        
        die();
    }
}
//print_r($ticket);
if($ticket['type']==2)
{
    include template('holiday_Order');
}
else
{
    include template('sceneryOrder');
}

function seed()
{
list($msec, $sec) = explode(' ', microtime());
return (float) $sec;
}

?>
