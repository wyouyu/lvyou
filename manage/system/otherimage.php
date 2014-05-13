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
	$INI['slide']['slide_img11'] = upload_image('slide_img11', $INI['slide']['slide_img11'], 'slide');
	//$INI['slide']['slide_img2'] = upload_image('slide_img2', $INI['slide']['slide_img2'], 'slide');
	//$INI['slide']['slide_img3'] = upload_image('slide_img3', $INI['slide']['slide_img3'], 'slide');
	//$INI['slide']['slide_img4'] = upload_image('slide_img4', $INI['slide']['slide_img4'], 'slide');
	//$INI['slide']['slide_img5'] = upload_image('slide_img5', $INI['slide']['slide_img5'], 'slide');
	
	save_config();
	$value = Utility::ExtraEncode($INI);
	$table = new Table('system', array('value'=>$value));
	if ( $system ) $table->SetPK('id', 1);
	$flag = $table->update(array( 'value'));
    log_admin('system', '编辑幻灯片设置',$_POST);
	Session::Set('notice', '更新幻灯片信息成功');
	redirect( null );
}

include template("manage_system_slide_other");
?>
