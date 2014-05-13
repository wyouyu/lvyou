<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

//need_partner();
$partner_id = abs(intval($_SESSION['partner_id']));
$action=$_GET['action'];
$typed = $_GET['type'];
if($action=='validate')
{
    $order = Table::Fetch('order',$_GET['oid']);
    $shidao = intval($_GET['content']);
    if($shidao>$order['quantity'])
    {
        //exit('123');
        json('实到人数不能大于购票数量', 'alert');
        //Session::Set('error','实到人数不能大于购票数量');
    }
}
if(!isset($_POST['submit']))
{
    $id = $order_id = abs(intval($_GET['id']));
    $html = render('manage_ajax_dialog_baoshu',array('orderid'=>$id,'action'=>$action,'typed'=>$typed));
    json($html, 'dialog');  
}
else
{
    $orderid = $_POST['orderid'];
    $shidao = intval($_POST['renshu']);
    
    
    $status = $shidao==0?2:3;
    Table::UpdateCache('order', $orderid, array(
                'shidao' => $shidao,
                'status'=>$status
                ));
    if($action='manage')
    {
        //exit($typed);
        if($typed==2)
        {
            redirect('/manage/order/dujiacun.php');
        }
        else if($typed==1)
        {
            redirect('/manage/order/index.php');
        }
        
    }
    redirect('/biz/index.php');
}

?>
