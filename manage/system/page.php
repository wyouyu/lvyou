<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$pages = array(
	'help_tour' => '玩转' . $INI['system']['abbreviation'],
	'help_faqs' => '常见问题',
	'help_zuitu' => '什么是' . $INI['system']['abbreviation'],
	'help_api' => '开发API',
	'about_contact' => '联系方式',
	'about_us' => '关于' . $INI['system']['abbreviation'],
	'about_job' => '工作机会',
	'about_terms' => '用户协议',
	'about_privacy' => '隐私声明',
);

$id = strval($_GET['id']);
if ( $id && !in_array($id, array_keys($pages))) { 
	redirect( WEB_ROOT . "/manage/system/page.php");
}
$n = Table::Fetch('page', $id);

if ( $_POST ) {
	$table = new Table('page', $_POST);
	$table->SetStrip('value');
	if ( $n ) {
		$table->SetPk('id', $id);
		$table->update( array('id', 'value') );
	} else {
		$table->insert( array('id', 'value') );
	}
    log_admin('system', '编辑页面:'.$pages[$id],$_POST);
	Session::Set('notice', "页面：{$pages[$id]}编辑成功");
	redirect( WEB_ROOT . "/manage/system/page.php?id={$id}");
}

$value = $n['value'];
include template('manage_system_page');
