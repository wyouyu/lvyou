<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager(true);

$system = Table::Fetch('system', 1);

if ($_POST) {
	unset($_POST['commit']);
	$INI = Config::MergeINI($INI, $_POST);
	if ( !save_config('php') ) {
		Session::Set('notice', '保存失败，'.SYS_PHPFILE.' 不可写');
	} else {
		$INI = ZSystem::GetUnsetINI($INI);
		$value = Utility::ExtraEncode($INI);
		$table = new Table('system', array('value'=>$value));
		if ( $system ) $table->SetPK('id', 1);
		$flag = $table->update(array( 'value'));
        log_admin('system', '编辑缓存设置',$_POST);
		Session::Set('notice', '更新系统信息成功');
	}
	redirect( WEB_ROOT . '/manage/system/cache.php');	
}

include template('manage_system_cache');
