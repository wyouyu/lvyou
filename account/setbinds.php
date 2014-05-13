<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();

$condition = array( 
		      'enable' => 'Y',
		      'user_id' => $login_user_id,
		);

$havemobile = DB::GetTableRow ('toolsbind', $condition);


$pagetitle = "手机绑定";
include template('account_setbinds');
