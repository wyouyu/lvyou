<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager(true);
$system = Table::Fetch('system', 1);

if ($_POST) {
	unset($_POST['commit']);
	if ($_POST['skin']['template']=='default') {
		$_POST['skin']['template']==null;
	}
	if ($_POST['skin']['theme']=='default') {
		$_POST['skin']['theme']==null;
	}
	$INI = Config::MergeINI($INI, $_POST);
	$INI = ZSystem::GetUnsetINI($INI);
	save_config();

	$value = Utility::ExtraEncode($INI);
	$table = new Table('system', array('value'=>$value));
	if ( $system ) $table->SetPK('id', 1);
	$flag = $table->update(array( 'value'));

	Session::Set('notice', '更新系统信息成功');
	redirect( WEB_ROOT . '/manage/system/skin.php');	
}

include template('manage_system_skin');
