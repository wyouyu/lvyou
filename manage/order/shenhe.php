<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('order');

$type=$_GET['type'];
$id = $_GET['id'];
$order = Table::Fetch('order',$id);
if($type==1)
{
    Table::UpdateCache('order', $id, array(
        'shenhe' => 2,
    ));
	//ZCoupon::TicketCreate($order,'',$shanghu=1);//给商户发送短信
    ZCoupon::TicketCreate($order);//发送短信验证码
    showmessage('审核成功,同时会向客户发送验证码','/manage/order/dujiacun.php');
}
else
{
    Table::UpdateCache('order', $id, array(
        'shenhe' => 3,
        'status'=>2
    ));
    showmessage('审核成功,此订单已作废','/manage/order/dujiacun.php');
}
?>
