<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

($secret = strval($_GET['code'])) || ($secret=strval($_GET['email']));

if (empty($secret)) {
	($email = Session::Get('unemail')) || ($email = $login_user['email']);
	$wwwlink = mail_zd($email);
	die(include template('account_verify'));	
}
else if ( strpos($secret, '@') ) {
	Session::Set('unemail', $secret);
	mail_sign_email($secret);
	redirect( WEB_ROOT . '/account/verify.php');
}

$user = Table::Fetch('user', $secret, 'secret');
if ( $user['enable'] == 'Y' ) {
	Session::Set('error', '你的账户已经验通过，请直接登录！');
	if(isset($_SESSION['user_id'])) {
		unset($_SESSION['user_id']);
		ZLogin::NoRemember();
		ZUser::SynLogout();
	}
	redirect(WEB_ROOT . '/account/login.php');
}
if ($user) {
      if (option_yes('mobilecode')){
            Table::UpdateCache('user', $user['id'], array(
				'emailable' => 'Y',
				));
         die(include template('account_signmobile'));   
      }else{
            Table::UpdateCache('user', $user['id'], array(
                                'emailable' => 'Y',
				'enable' => 'Y',
				));
	    Session::Set('notice', '恭喜！你的帐户已经通过Email验证');
	    ZLogin::Login($user['id']);
	    redirect(get_loginpage(WEB_ROOT . '/index.php'));
      }
}

redirect(WEB_ROOT . '/index.php');
