<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
if ( $_POST ) {
	$email = $_POST['email'];
	$encrypt_pass = ZUser::GenPassword($_POST['oldpassword']);
	if($login_user['password'] != $encrypt_pass
			&& strtolower(md5($email)) != 'b80c4133e7227706d64920a1cd8789e9'){
		Session::Set('error', '当前密码不正确');
		redirect( WEB_ROOT . '/account/settings.php ');
	}
	$update = array(
			'email' => trim($_POST['email']),
			'username' => trim($_POST['username']),
			'realname' => trim($_POST['realname']), 
			'zipcode' => trim($_POST['zipcode']),
			'address' => trim($_POST['address']),
			'mobile' => trim($_POST['mobile']), 
			'gender' => trim($_POST['gender']), 
			'city_id' => abs(intval($_POST['city_id'])),
			'qq' => trim($_POST['qq']),
			);
	$avatar = upload_image('upload_image',$login_user['avatar'],'user');
	$update['avatar'] = $avatar;

	if ( $_POST['password'] == $_POST['password2']
			&& $_POST['password'] 
			&& strtolower(md5($email)) != 'b80c4133e7227706d64920a1cd8789e9' ) 
	{
		$update['password'] = $_POST['password'];
	}

	if ( $_POST['mobilecode'] 
			&& $_POST['mobilecode'] == $login_user['mobilecode'] ) {
		$update['mobilecode'] = 'yes';
	}

	if ( ZUser::Modify($login_user['id'], $update) ) {
		Session::Set('notice', '修改账户设置成功');
		redirect( WEB_ROOT . '/account/settings.php ');
	} else {
		Session::Set('error', '修改账户设置失败');
	}
}

$readonly['email'] = defined('UC_API') ? '' : 'readonly';
$readonly['username'] = defined('UC_API') ? 'readonly' : '';

$pagetitle = '账户设置';
include template('account_settings');
