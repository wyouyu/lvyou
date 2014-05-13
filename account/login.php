<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if ( $_POST ) {
	$login_user = ZUser::GetLogin($_POST['email'], $_POST['password']);
	if ( !$login_user ) {
		Session::Set('error', '登录失败');
		redirect(WEB_ROOT . '/account/login.php');
	} else if (option_yes('mobilecode')
		        && $login_user['emailable']=='Y'
			&& $login_user['enable']=='N'
			) {
                $user['id'] = $login_user['id'];
		Session::Set('error', '请验证手机号');
		die(include template('account_signmobile')); 
	}
          else if (option_yes('emailverify')
                        && $login_user['emailable']=='N'
			&& $login_user['enable']=='N'
			&& $login_user['secret']
			) {
		Session::Set('unemail', $_POST['email']);
		redirect(WEB_ROOT .'/account/verify.php');
	} else {
		Session::Set('user_id', $login_user['id']);
		if (abs(intval($_POST['auto_login']))) {
			ZLogin::Remember($login_user);
		}
		ZUser::SynLogin($login_user['username'], $_POST['password']);
		ZCredit::Login($login_user['id']);
		redirect(get_loginpage(WEB_ROOT . '/index.php'));
	}
}
$currefer = strval($_GET['r']);
if ($currefer) { Session::Set('loginpage', udecode($currefer)); }
$pagetitle = '登录';
include template('user_login');
