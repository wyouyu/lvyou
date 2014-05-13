<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
//require_once(dirname(__FILE__) . '/current.php');

need_manager();
need_auth('news');

$id = abs(intval($_GET['id']));
$news  = Table::Fetch('news', $id);

if ( is_get() && empty($news) ) {
	$news = array();
	$news['id'] = 0;
	$news['begin_time'] = strtotime('+0 days');

}
else if ( is_post() ) {
	$news = $_POST;
	$insert = array(
		'title', 
		'begin_time', 
		'detail',
		);
	
	$news['begin_time'] = strtotime($news['begin_time']);
	$insert = array_unique($insert);
	$table = new Table('news', $news);
	$table->SetStrip('detail');

	if ( $news['id'] && $news['id'] == $id ) {
		$table->SetPk('id', $id);
		$table->update($insert);
		Session::Set('notice', '添加新闻成功！');
		redirect( WEB_ROOT . "/manage/news/index.php");
	} 
	else if ( $news['id'] ) {
		Session::Set('error', '非法编辑');
		redirect( WEB_ROOT . "/manage/news/index.php");
	}

	if ( $table->insert($insert) ) {
		Session::Set('notice', '添加新闻成功');
		redirect( WEB_ROOT . "/manage/news/index.php");
	}
	else {
		Session::Set('error', '编辑新闻失败');
		redirect(null);
	}
}


$selector = $news['id'] ? 'edit' : 'create';
include template('manage_news_edit');
function current_managenews($selector='edit', $id=0) {
	$selector = $selector ? $selector : 'edit';
	$a = array(
		"/manage/news/edit.php?id={$id}" => '基本信息',
	);
	$l = "/manage/news/{$selector}.php?id={$id}";
	return current_link($l, $a);
}

