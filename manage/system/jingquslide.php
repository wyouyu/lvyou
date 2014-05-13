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
	$INI['slide']['slide_img6'] = upload_image('slide_img6', $INI['slide']['slide_img6'], 'slide');
	$INI['slide']['slide_img7'] = upload_image('slide_img7', $INI['slide']['slide_img7'], 'slide');
	$INI['slide']['slide_img8'] = upload_image('slide_img8', $INI['slide']['slide_img8'], 'slide');
	$INI['slide']['slide_img9'] = upload_image('slide_img9', $INI['slide']['slide_img9'], 'slide');
	$INI['slide']['slide_img10'] = upload_image('slide_img10', $INI['slide']['slide_img10'], 'slide');
	
	save_config();
	$value = Utility::ExtraEncode($INI);
	$table = new Table('system', array('value'=>$value));
	if ( $system ) $table->SetPK('id', 1);
	$flag = $table->update(array( 'value'));
    log_admin('system', '编辑幻灯片设置',$_POST);
	Session::Set('notice', '更新幻灯片信息成功');
	redirect( null );
}

include template("manage_system_slide_jingqu");
?>
