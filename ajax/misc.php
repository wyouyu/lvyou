<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_manager();

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));
$v = strval($_GET['v']);


if ( 'sms' == $action ) {
	$mobile = $v;
	$html = render('manage_ajax_dialog_miscsms');
	json($html, 'dialog');
}
elseif ( 'tianxie' == $action ) {
    $id = $v;
    $html = render('manage_ajax_dialog_tianxie',$id);
    json($html, 'dialog');
}
else if( 'smssend' == $action ) {
	$mobile = $v;
	$content = strval($_GET['content']);
	if ( true=== ($ret = sms_send($mobile, $content))  ) {
		json('call_succ();', 'eval');
	}
	json('call_fail("'.$ret.'");', 'eval');
}
else if ( 'imageremove' == $action ) {
	$team = Table::Fetch('team', $id);
	$field = "image{$v}";
	$relpath = $team[$field];
	$abspath = WWW_ROOT . '/static/' . $relpath;
	if ( file_exists($abspath) && @unlink($abspath) ) {
		Table::UpdateCache('team', $id, array(
					$field => null, 
					));
		json(array(
					array('data'=>'删除图片成功', 'type'=>'alert',),
					array('data'=>"X.team.imageremovecall({$v});", 'type'=>'eval',),
				  ), 'mix');
	}
	json('删除图片失败', 'alert');
}
else if ( 'link' == $action ) {
	if ($id) $link = Table::Fetch('friendlink', $id);
	$html = render('manage_ajax_dialog_misclink');
	json($html, 'dialog');
}
else if ( 'linkremove' == $action ) {
	if ($id) $link = Table::Fetch('friendlink', $id);
	if (!$link) json('输入信息不正确', 'alert');
	Table::Delete('friendlink', $id);
	Session::Set('notice', '删除链接成功');
	json(null, 'refresh');
}
