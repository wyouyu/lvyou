<?php
require_once(dirname(__FILE__) . '/app.php');
//$order_id = intval($_GET['id']);
need_login();
if($_POST)
{
	//exit('000');

	$data['money'] =$_POST['money'];
	$data['beizhu'] = trim($_POST['beizhu']);
	$data['uid'] = $login_user_id;
	$data['create_time'] = time();
	$data['status'] = 0;
	$user = Table::Fetch('user', $data['uid']);
	if($data['money']>$user['money'])
	{
		showmessage('提现失败,账户余额不足','/account/memberaccount.php');
	}
	$table = new Table('tixian',$data);
	//exit(print_r($data));
	$inert_array = array('money','beizhu','uid','create_time');
	if($table->insert($inert_array))
	{
		
		
		
		Table::UpdateCache('user', $data['uid'], array(
					'money' => array( "money - {$data['money']}" ),
					));
		showmessage('提现申请成功，请耐心登录，并关注支付宝余额','/account/memberaccount.php');
	}
	
}
include template('tixian');

