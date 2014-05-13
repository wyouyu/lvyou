<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$id = abs(intval($_GET['id']));
$user = Table::Fetch('user', $id);

//do not change the default user's email
if($user['email']=='demo@zuitu.com'){
	$_POST['email']='demo@zuitu.com';
}

if ( $_POST && $id==$_POST['id'] ) {
	$table = new Table('user', $_POST);
	$up_array = array(
			'email', 'username', 'realname', 
			'mobile', 'zipcode', 'address',
			'secret', 'qq',
			);

	// unique email per user
	$email = $_POST['email'];
	$eu = Table::Fetch('user', $email, 'email');
	if ($eu && $eu['id'] != $id ) {
		Session::Set('notice', 'Email地址已经存在,不能修改');
		redirect( WEB_ROOT . "/manage/user/index.php");
	}
	// unique username per user
	$username = strval($_POST['username']);
	$eu = Table::Fetch('user', $username, 'username');
	if ($eu && $eu['id'] != $id ) {
		Session::Set('notice', '用户名已经存在,不能修改');
		redirect( WEB_ROOT . "/manage/user/index.php");
	}
	// end


	if ( $login_user_id == 1 && $id > 1 ) { $up_array[] = 'manager'; }
	if ( $id == 1 && $login_user_id > 1 ) {
		Session::Set('notice', '你无权修改超级管理员信息');
		redirect( WEB_ROOT . "/manage/user/index.php");
	}
	$table->manager = (strtoupper($table->manager)=='Y') ? 'Y' : 'N';
	if ($table->password ) {
		$table->password = ZUser::GenPassword($table->password);
		$up_array[] = 'password';
	}
	$flag = $table->update( $up_array );
	if ( $flag ) {
		Session::Set('notice', '修改用户信息成功');
		redirect( WEB_ROOT . "/manage/user/edit.php?id={$id}");
	}
	Session::Set('error', '修改用户信息失败');
	$user = $_POST;
}

include template('manage_user_edit');
