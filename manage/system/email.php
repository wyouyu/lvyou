<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager(true);

$system = Table::Fetch('system', 1);

if ($_POST) {
	unset($_POST['commit']);
	$INI = Config::MergeINI($INI, $_POST);
	
	$INI = ZSystem::GetUnsetINI($INI);
	//
	save_config();
	//exit(print_r($INI));
	$value = Utility::ExtraEncode($INI);
	//exit(print_r($value));
	$table = new Table('system', array('value'=>$value));
	if ( $system ) $table->SetPK('id', 1);
	$flag = $table->update(array( 'value'));
    log_admin('system', '编辑邮件设置',$_POST);
	Session::Set('notice', '更新系统信息成功');
	//exit(print_r($INI));
	redirect( WEB_ROOT . '/manage/system/email.php');	
}

include template('manage_system_email');
