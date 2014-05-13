<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$id = abs(intval($_REQUEST['id']));
$friendlink = Table::Fetch('friendlink', $id);
$table = new Table('friendlink', $_POST);
$table->letter = strtoupper($table->letter);
$table->display = strtoupper($table->display)=='Y' ? 'Y' : 'N';
$uarray = array( 'title','url','logo','sort_order', 'display'); 

if (!$_POST['title'] || !$_POST['url'] ) {
	Session::Set('error', '网站名称、网站网址均不能为空');
	redirect(null);
}

if ( $friendlink ) {
	if ( $flag = $table->update( $uarray ) ) {
		Session::Set('notice', '编辑链接成功');
	} else {
		Session::Set('error', '编辑链接失败');
	}
} else {
	if ( $flag = $table->insert( $uarray ) ) {
		Session::Set('notice', '新建链接成功');
	} else {
		Session::Set('error', '新建链接失败');
	}
}

redirect(null);
