<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$action = strval($_GET['action']);

if ( 'unsubscribe' == $action ) {
	$html = render('ajax_dialog_smsun');
	json($html, 'dialog');
}
elseif ( 'unsubscribecheck' == $action ) {
	$mobile = trim(strval($_GET['mobile']));
	$verifycode= trim(strval($_GET['verifycode']));
	if ( Utility::CaptchaCheck($verifycode) ) {
		$sms = Table::Fetch('smssubscribe', $mobile, 'mobile');
		if ( !$sms ) {
			$html = render('ajax_dialog_smsunsuc');
		} else if ( $sms['enable'] == 'N' ) {
			ZSMSSubscribe::UnSubscribe($mobile);
			$html = render('ajax_dialog_smsunsuc');
		} else {
			$secret = ZSMSSubscribe::Secret($mobile);
			$html = render('ajax_dialog_smscode');
			sms_secret($mobile, $secret, false);
		}
		json($html, 'dialog');
	} else {
		json( 'captcha_again();', 'eval' );
	}
}
else if ( 'subscribe' == $action ) {
	$html = render('ajax_dialog_smssub');
	json($html, 'dialog');
} 
elseif ( 'subscribecheck' == $action ) {
	$mobile = trim(strval($_GET['mobile']));
	$verifycode= trim(strval($_GET['verifycode']));
	$city_id = abs(intval($_GET['city_id']));
	$secret = Utility::VerifyCode();
	if ( Utility::CaptchaCheck($verifycode) ) {
		if ( ZSMSSubscribe::Create($mobile, $city_id, $secret) === true ) {
			$html = render('ajax_dialog_smssuc');
		} else {
			$html = render('ajax_dialog_smscode');
			sms_secret($mobile, $secret, true);
		}
		json($html, 'dialog');
	} else {
		json( 'captcha_again();', 'eval' );
	}
}
else if ( 'codeyes' == $action ) {
	$mobile = trim(strval($_GET['mobile']));
	$secretcode= trim(strval($_GET['secretcode']));
	$sms = Table::Fetch('smssubscribe', $mobile, 'mobile');
	if ( !$sms ) {
		json(array(
					array('data' => '非法访问！', 'type'=>'alert'),
					array('data' => 'X.boxClose();', 'type'=>'eval'),
				  ), 'mix');
	}

	if ($sms['secret'] != $secretcode) {
		json('短信认证码不正确，请重新输入！', 'alert');
	}

	if ($sms['enable'] == 'Y') {
		ZSMSSubscribe::Unsubscribe($mobile);
		$html = render('ajax_dialog_smsunsuc');
		json($html, 'dialog');
	}
	else {
		ZSMSSubscribe::Enable($mobile, true);
		$html = render('ajax_dialog_smssuc');
		json($html, 'dialog');
	}
}
else if ( 'bindmobile' == $action ) {
	$userid = strval($_GET['userid']);
    $html = render('ajax_dialog_smsbind');
	json($html, 'dialog');
}
else if ( 'mobilebindcheck' == $action ) {
	$mobile = trim(strval($_GET['mobile']));
	$verifycode= trim(strval($_GET['verifycode']));
	$user_id = abs(intval($_GET['user_id']));
	$secret = Utility::VerifyCode();
	if ( Utility::CaptchaCheck($verifycode) ) {
		if ( ZToolsbind::Create($mobile, $user_id, $secret) === true ) {
               //json('该手机号已经存在,如果继续绑定,原账号将被取消！', 'confirm');
                          $html = render('ajax_dialog_smsbindcode');
			  sms_secret($mobile, $secret, true);
		} else {
			  $html = render('ajax_dialog_smsbindcode');
			  sms_secret($mobile, $secret, true);
		}
		json($html, 'dialog');
	} else {
		json( 'captcha_again();', 'eval' );
	}
}
else if ( 'bindcodeyes' == $action ) {
	$mobile = trim(strval($_GET['mobile']));
	$user_id = abs(intval($_GET['user_id']));
	$secretcode= trim(strval($_GET['secretcode']));
	$condition = array( 
		      'tools' => $mobile, 
		      'enable' => 'N',
		      'user_id' => $user_id,
		);
	//json($user_id, 'alert');
	$sms = DB::GetTableRow ('toolsbind', $condition);
	if ( !$sms ) {
		json(array(
			array('data' => '非法访问！', 'type'=>'alert'),
			array('data' => 'X.boxClose();', 'type'=>'eval'),
				  ), 'mix');
	}

	if ($sms['secret'] != $secretcode) {
		json('短信认证码不正确，请重新输入！', 'alert');
	}else {
		ZToolsbind::Enable($mobile, true);
                ZLogin::Login($sms['user_id']);
                json( array(
			array('data'=>'绑定成功', 'type' => 'alert',),
			array('data'=>'X.boxClose();', 'type' => 'eval',),
			array('data'=> 'window.location=  "/index.php";', 
    'type' =>'eval',),
                        //array('data'=>'null', 'type' => 'refresh',),
			   ), 'mix');
	   redirect(WEB_ROOT . '/index.php');
	}
}
else if ( 'loginbindmobile' == $action ) {
	$userid = strval($_GET['userid']);
        $html = render('ajax_dialog_smsloginbind');
	json($html, 'dialog');
}
else if ( 'loginmobilebindcheck' == $action ) {
	$mobile = trim(strval($_GET['mobile']));
	$verifycode= trim(strval($_GET['verifycode']));
	$user_id = abs(intval($_GET['user_id']));
	$secret = Utility::VerifyCode();
	if ( Utility::CaptchaCheck($verifycode) ) {
	if ( ZToolsbind::Create($mobile, $user_id, $secret) === false ) {
              json('您已经绑定了该手机号！', 'alert');
		} else {
			  $html = render('ajax_dialog_smsloginbindcode');
			  sms_secret($mobile, $secret, true);
		}
		json($html, 'dialog');
	} else {
		json( 'captcha_again();', 'eval' );
	}
}
else if ( 'loginbindcodeyes' == $action ) {
	$mobile = trim(strval($_GET['mobile']));
	$user_id = abs(intval($_GET['user_id']));
	$secretcode= trim(strval($_GET['secretcode']));
	$condition = array( 
		      'tools' => $mobile, 
		      'enable' => 'N',
		      'user_id' => $user_id,
		);
	//json($user_id, 'alert');
	$sms = DB::GetTableRow ('toolsbind', $condition);
	if ( !$sms ) {
		json(array(
	        	array('data' => '非法访问！', 'type'=>'alert'),
			array('data' => 'X.boxClose();', 'type'=>'eval'),
				  ), 'mix');
	}

	if ($sms['secret'] != $secretcode) {
		json('短信认证码不正确，请重新输入！', 'alert');
	}else {
		ZToolsbind::Enable($mobile, true,$user_id);
        json( array(
		        array('data'=>'绑定成功', 'type' => 'alert',),
			array('data'=>'X.boxClose();', 'type' => 'eval',),
			array('data'=>'null', 'type' => 'refresh',),
			   ), 'mix');
	}
}
