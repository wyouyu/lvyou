<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
$pagetitle = "收货地址管理";

$condition = array(
	'user_id' => $login_user_id,
);

$add = DB::LimitQuery('address', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
));

if($_POST){
	$address = $_POST;
	$user_id = $login_user_id;
	if(ZUser::Address($user_id,$address)){
		Session::Set('notice', '新建收货地址成功');
		redirect( WEB_ROOT . '/account/setaddress.php ');
	}else{
		Session::Set('error', '新建收货地址失败');
	}
}

include template('account_setaddress');
