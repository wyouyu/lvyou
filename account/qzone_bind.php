<?php
@header('Content-Type:text/html;charset=utf-8'); 
session_start();
require_once(dirname(dirname(__FILE__)) . '/thirdpart/qzone/config.php' );
require_once(dirname(dirname(__FILE__)) . '/thirdpart/qzone/txwboauth.php' );
require_once(dirname(dirname(__FILE__)) . '/app.php');
$c = new WeiboClient( WB_AKEY , WB_SKEY , $_SESSION['last_key']['oauth_token'] , $_SESSION['last_key']['oauth_token_secret']  );

$ms=$c->pub_abc($_SESSION['last_key']['openid']);

$id = $_SESSION['last_key']['openid'];
$name = $ms['nickname'];

if(!$id) {
	need_login();
}

$type = "qzone";
$sns = "qzone:".$id;
$exist_user = Table::Fetch('user', $sns, 'sns');
if ( $exist_user ) {
	Session::Set('user_id', $exist_user['id']);
	Utility::Redirect(get_loginpage(WEB_ROOT . '/index.php'));
}


if ( $_POST ){
	if($_POST['sns_action']=='bind'){
		$email = $_POST['email'];
		if ( ! Utility::ValidEmail($email, true) ) {
		Session::Set('error', 'Email地址为无效地址');
		redirect( WEB_ROOT . '/account/qzone_bind.php');
	    }
		if($_POST['password']==""){
		Session::Set('error', '请输入正确密码');
		redirect( WEB_ROOT . '/account/qzone_bind.php');
		}
		$encrypt_pass = ZUser::GenPassword($_POST['password']);
        $update = array(
			'sns' => $sns,			
		);	
        $sns_bind = Table::Fetch('user', $email, 'email');
		if(!$sns_bind) {
		Session::Set('error', '邮箱输入不正确');
		Utility::Redirect(WEB_ROOT . '/account/qzone_bind.php' ); 
		} 
	    if($sns_bind['password'] != $encrypt_pass){	
		Session::Set('error', '密码输入不正确');
		Utility::Redirect(WEB_ROOT . '/account/qzone_bind.php' );
		}
		if ( $sns_bind['sns'] ) {
		Session::Set('error', '绑定失败，Email已绑定');
	    Utility::Redirect(WEB_ROOT . '/account/qzone_bind.php' );
		}	
        if(ZUser::Modify($sns_bind['id'], $update)){
	 	Session::Set('user_id', $sns_bind['id']);
	    Utility::Redirect(get_loginpage(WEB_ROOT . '/index.php'));
	 	}
	}else{
	 $mobile = $_POST['mobile'];
	 $email  = $_POST['email'];
	 $have_email = Table::Fetch('user', $email, 'email');
     if($have_email) {
		Session::Set('error', '该邮箱已被注册');
		Utility::Redirect(WEB_ROOT . '/account/qzone_bind.php' ); 
		}
	 $username = $ms['nickname'];
	 $have_user = Table::Fetch('user', $username, 'username');
     while(!empty($have_user)) {
	 $username = $ms['nickname'] .'_' . rand(100,999);
	 $have_user = Table::Fetch('user', $username, 'username');
        }
	 $new_user = array(
		'username' => $username,
		'email' => $email,
		'mobile' => $mobile,
		'password' => $_POST['password'],
		'sns' => $sns,
      );
	 $user_sns['id'] = ZUser::Create($new_user, true);
	 Session::Set('user_id', $user_sns['id']);
	 Utility::Redirect(get_loginpage(WEB_ROOT . '/index.php'));
 }
}


include template('account_sina_bind');

