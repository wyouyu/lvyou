<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
$id = intval($_GET['id']);
$type =intval($_GET['type']);
$order = Table::Fetch('order',$id);

$partner = Table::Fetch('partner',$order['partner_id']);
if($_POST['action'])
{
    $table = new Table('comment', $_POST);
    $table->c_time = time();
    $table->partner_id = $partner['id'];
    $table->userid = $order['user_id'];
	$team['image1'] = upload_image('upload_image1','','team',true);
	$team['image2'] = upload_image('upload_image2','','team',true);
	$team['image3'] = upload_image('upload_image3','','team',true);
	$images  = '';
	if($team['image1'])
	{
		$images .=$team['image1'];
	}
	if($team['image2'])
	{
		$images .='|'.$team['image1'];
	}
	if($team['image3'])
	{
		$images .='|'.$team['image3'];
	}
	$table->images  = $images;
	//exit(print_r($team['image1']));
    if($_GET['type']==2){
        $table->insert(array('orderid','t_time','c_time','userid','partner_id','tuijian','content','zongpingfen','images'));
    }
    else
    {
        $table->insert(array('orderid','t_time','c_time','userid','partner_id','title','content','service','other','traffic','feel','fuwunum','bianjienum','youhuinum','is_youyong','images'));
    }
    
    if($_GET['type']==2)
		showmessage('评论成功','/order/dujiaorder.php');
	else
		showmessage('评论成功','/order/index.php');
}
if($type==2)
{
    $current ='dujiacun';
    include template('comment_dujia');
}
else
{
    $current ='index';
    include template('comment');
}

?>
