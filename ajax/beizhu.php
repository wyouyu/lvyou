<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
$action= $_GET['action'];
if(!isset($_POST['submit']))
{
    $id = $order_id = abs(intval($_GET['id']));
	$order = Table::Fetch('order',$id);
    $html = render('manage_ajax_dialog_beizhu',array('orderid'=>$id));
    json($html, 'dialog');  
}
else
{
    $orderid = $_POST['orderid'];
    $content = $_POST['contents'];
    
    Table::UpdateCache('order', $orderid, array(
                'beizhu' => $content,
                ));
	if($action=='dujiacun')
	{
		redirect('/manage/order/dujiacun.php');
	}
	else
		redirect('/manage/order/index.php');
    
}

?>
