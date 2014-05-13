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
	$INI['slide']['slide_img12'] = upload_image('slide_img12', $INI['slide']['slide_img12'], 'slide');
	$INI['slide']['slide_img13'] = upload_image('slide_img13', $INI['slide']['slide_img13'], 'slide');
	$INI['slide']['slide_img14'] = upload_image('slide_img14', $INI['slide']['slide_img14'], 'slide');
	$INI['slide']['slide_img15'] = upload_image('slide_img15', $INI['slide']['slide_img15'], 'slide');
	$INI['slide']['slide_img16'] = upload_image('slide_img16', $INI['slide']['slide_img16'], 'slide');
	$INI['slide']['slide_img17'] = upload_image('slide_img17', $INI['slide']['slide_img17'], 'slide');
	$INI['slide']['slide_img18'] = upload_image('slide_img18', $INI['slide']['slide_img18'], 'slide');
	$INI['slide']['slide_img19'] = upload_image('slide_img19', $INI['slide']['slide_img19'], 'slide');
	save_config();
	$value = Utility::ExtraEncode($INI);
	$table = new Table('system', array('value'=>$value));
	if ( $system ) $table->SetPK('id', 1);
	$flag = $table->update(array( 'value'));
    log_admin('system', '编辑幻灯片设置',$_POST);
	Session::Set('notice', '更新幻灯片信息成功');
	redirect( null );
}

include template("manage_system_scenery");
?>
