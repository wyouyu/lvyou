<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if ( $_POST ) {
  $userid = $_POST['userid'];
  $update['mobile'] = $_POST['mobile'];
  $user = Table::Fetch('user', $userid, 'id');
  if ( $_POST['verifycode'] 
			&& $_POST['verifycode'] == $user['mobilecode'] ) {
		$update['mobilecode'] = 'yes';
	}
  ZUser::Modify($user['id'], $update);

  $user = Table::FetchForce('user', $userid);
  if ($user['mobilecode'] == 'yes' ) {        
       // Session::Set('notice', '手机验证成功');
        ZLogin::Login($user['id']);
        include template('account_bindmobilesuccess');
	} else {
	Session::Set('error', '手机验证失败');
        include template('account_signmobile');
	}
}
