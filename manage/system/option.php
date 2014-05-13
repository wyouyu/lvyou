<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$s = isset($_GET['s']) ? strval($_GET['s']) : null;
$ts = $s ? '_' . $s : null;

$system = Table::Fetch('system', 1);

if ($_POST) {
	need_manager(true);
	unset($_POST['commit']);
	$INI = Config::MergeINI($INI, $_POST);
	$INI = ZSystem::GetUnsetINI($INI);
	save_config();

	$value = Utility::ExtraEncode($INI);
	$table = new Table('system', array('value'=>$value));
	if ( $system ) $table->SetPK('id', 1);
	$flag = $table->update(array( 'value'));
    log_admin('system', '编辑选项设置',$_POST);
	Session::Set('notice', '更新系统信息成功');
	redirect(null);	
}

include template("manage_system_option{$ts}");

function current_system_option($s=null) {
	global $city;
	if (option_yes('daysign')){
		$filter = array(
			'a' => '页面设置',
			'b' => '地址重写',
			'c' => '登录设置',
			'd' => '每日签到设置',
	    );
	}else{
		$filter = array(
			'a' => '页面设置',
			'b' => '地址重写',
			'c' => '登录设置',
		);
	}
	$a['/manage/system/option.php'] = '基本选项';
	foreach($filter AS $id=>$name) {
		$a["/manage/system/option.php?s={$id}"] = $name;
	}
	$l = '/manage/system/option.php';
	if ($s) $l = "/manage/system/option.php?s={$s}";
	return current_link($l, $a, true);
}
