<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();

$action = strval($_GET['action']);
$id = strval($_GET['id']);
$v = strval($_GET['v']);

if ( 'bindcancel' == $action ) {
	Table::UpdateCache('user', $login_user['id'], array(
				'mobilecode' => null,
				));
	json(array(
				array( 'data'=>'取消手机绑定成功', 'type' => 'alert'),
				array( 'data'=>null, 'type' => 'refresh'),
			  ),	'mix');
}
else if( 'deladd' == $action){
	Table::Delete('address', $id);
	json(array(
				array( 'data'=>'删除地址成功', 'type' => 'alert'),
				array( 'data'=>null, 'type' => 'refresh'),
			  ),	'mix');
}
else if( 'setdefault' == $action){
	DB::Update('address', array('user_id' => $login_user['id'],'default'=>'Y'), array(
				'default' => 'N',
				));
	DB::Update('address', array('id' => $id), array(
				'default' => 'Y',
				));
	json(array(
				array( 'data'=>'设置成功', 'type' => 'alert'),
				array( 'data'=>null, 'type' => 'refresh'),
			  ),	'mix');
}
