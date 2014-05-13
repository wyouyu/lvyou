<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if($_POST)
{
	$email = $_POST['email'];
	$encrypt_pass = ZUser::GenPassword($_POST['oldpassword']);
	if($login_user['password'] != $encrypt_pass){
		showmessage('当前密码不正确！',WEB_ROOT .'/account/memberinfo.php');
	}

	$update = array(
			'email' => trim($_POST['email']),
			'username' => trim($_POST['username']),
			'realname' => trim($_POST['realname']), 
			//'zipcode' => trim($_POST['zipcode']),
			//'address' => trim($_POST['address']),
			'mobile' => trim($_POST['mobile']), 
			'gender' => trim($_POST['gender']), 
			//'city_id' => abs(intval($_POST['city_id'])),
			'qq' => trim($_POST['qq']),
			'job' =>intval($_POST['ddlZhiye']),
			'idcardnum'=>trim($_POST['idcardnum']),
			);
	if ( $_POST['password'] == $_POST['password2']&& $_POST['password']) 
	{
		$update['password'] = $_POST['password'];
	}

	if ( ZUser::Modify($login_user['id'], $update) ) {
		showmessage('账户设置成功！',WEB_ROOT .'/account/memberinfo.php');
	} else {
		Session::Set('账户设置失败！', WEB_ROOT .'/account/memberinfo.php');
	}

}

$current = 'memberinfo';
include template('member_info');
?>
