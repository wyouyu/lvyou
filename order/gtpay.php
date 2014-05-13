<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');

$sceneryid = $_GET['sceneryid'];

$partner = DB::LimitQuery('partner', array(
    'condition' => 'id='.$sceneryid,
    //'order' => 'ORDER BY id DESC',
    'one' => true,
));

if(!empty($_POST['outtime']))
{
    $date_str = $_POST['outtime'];
}
else
{
    $date_str = date('Y-m-d',time());
}


if ( $_POST ) {
    $table = new Table('order', $_POST);
    if ( $table->quantity1 == 0 ) {
        showmessage('成人位数不能小于1份');
    }
    if($login_user_id)
    {
        $table->user_id = $login_user_id;
    }
    else
    {
        $table->user_id ='99999';//99999代表匿名购买
    }
   
    $table->outtime =strtotime($_POST['outtime']);
    $table->team_id = 0;
	$table->city_id = 0;
	$table->origin = 1;
	$table->price = 1;
	$table->money = 1;
	$table->tianshu = 1;
	$table->state = 1;
	$table->ptype = 1;
	$table->type = 5; //5代表跟团游
	$table->quantity = 1;
	$table->fadan = 2;
    $table->partner_id = $sceneryid;
    $table->create_time = time();
	$table->quantity1 = intval($_POST['quantity1']);
	$table->quantity2 = intval($_POST['quantity2']);
   
    $insert = array(
            'user_id', 'team_id', 'city_id', 'state','pay_id', 
            'origin', 'price','money','tianshu',
             'realname', 'mobile', 'partner_id','ptype',
            'quantity', 'create_time','outtime','type','lidiandate','s_type','fadan','smsid','quantity1','quantity2'
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
        $table->smsid = 'W'.seed();
        if ($flag = $table->Insert($insert)) {
            $order_id = DB::GetInsertId();
            $randid = strtolower(Utility::GenSecret(4, Utility::CHAR_WORD));
            $pay_id = "go-{$order_id}-{$table->quantity}-{$randid}";
            Table::UpdateCache('order', $order_id, array(
                'pay_id' => $pay_id,
                ));
            $order = Table::Fetch('order', $order_id);

			 Table::UpdateCache('order', $order_id, array(
				'shenhe' => 1,//标记为待审核阶段
			 ));
           
        }
        if($_POST['ntype']==2)
        {
           // Table::UpdateCache('order', $order_id, array(
              //  's_type' => 1,
               // ));
			  ZCoupon::TicketCreate($order,'',$shanghu=1);//给商户发送短信
            redirect(WEB_ROOT."/order/paysucc.php?id=".$order_id.'&type=2');
        }
        else
        {
            redirect(WEB_ROOT."/order/paysucc2.php?id=".$order_id);
        }
        
        die();
    }
}
include template('gentuanyou/gentuanorder');


function seed()
{
list($msec, $sec) = explode(' ', microtime());
return (float) $sec;
}

?>
