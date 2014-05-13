<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$root = DIR_TEMPLATE;
$template_id = trim(strval($_GET['id']));
$template_id = str_replace('\\', '_', $template_id);
$template_id = str_replace('/', '_', $template_id);

if ( $_POST ) {
	$path = "{$root}/{$template_id}";
	if(is_writable($path) && !is_dir($path) && is_file($path)) {
		$flag = file_put_contents($path, stripslashes(trim($_POST['content'])));
	}
	if ( $flag ) {
        log_admin('system', '编辑模板成功:'.$template_id);
		Session::Set('notice', "模板 {$template_id} 修改成功");
	} else {
        log_admin('system', '编辑模板失败:'.$template_id);
		Session::Set('error', "模板 {$template_id} 修改失败");
	}
	redirect(WEB_ROOT . "/manage/system/template.php?id={$template_id}");
}

$handle = opendir($root);
$may = array();
while($one = readdir($handle)) {
	if( is_dir("{$root}/{$one}") ) continue;
	if(!is_writable( "{$root}/{$one}") ) continue;
	$may[] = $one;
}
$may = array_combine($may, $may);
asort($may);

$template_file =  "{$root}/{$template_id}";
if (file_exists($template_file) && !is_dir($template_file)) {
	$content = trim(file_get_contents( "{$root}/{$template_id}" ));
} else {
	$template_id = null;
}

include template('manage_system_template');
