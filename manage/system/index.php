<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
$s = isset($_GET['s']) ? strval($_GET['s']) : null;
$ts = $s ? '_' . $s : null;

$system = Table::Fetch('system', 1);

if ($_POST) {
	need_manager(true);
	unset($_POST['commit']);
	$INI = Config::MergeINI($INI, $_POST);
	$INI = ZSystem::GetUnsetINI($INI);

	$INI['system']['gzip'] = abs(intval($INI['system']['gzip']>0));
	$INI['system']['partnerdown'] = abs(intval($INI['system']['partnerdown']>0));
	$INI['system']['conduser'] = abs(intval($INI['system']['conduser']>0));
	$INI['system']['currencyname'] = strtoupper($INI['system']['currencyname']);

	save_config();

	$value = Utility::ExtraEncode($INI);
	$table = new Table('system', array('value'=>$value));
	if ( $system ) $table->SetPK('id', 1);
	$flag = $table->update(array( 'value'));
    log_admin('system', '编辑基本设置',$_POST);
	Session::Set('notice', '更新系统信息成功');
	redirect( null );
}

include template("manage_system_index{$ts}");

function current_system_index($s=null) {
	global $city;
	$filter = array(
		'a' => 'AAAAAAA',
		'b' => 'BBBBBBBBB',
		'c' => 'CCCCCCCC',
		'd' => 'DDDDDDDDD',
		'e' => 'EEEEEEEEE',
	);
	$a['/manage/system/index.php'] = '基本';
	foreach($filter AS $id=>$name) {
		$a["/manage/system/index.php?s={$id}"] = $name;
	}
	$l = '/manage/system/index.php';
	if ($s) $l = "/manage/system/index.php?s={$s}";
	return current_link($l, $a, true);
}
