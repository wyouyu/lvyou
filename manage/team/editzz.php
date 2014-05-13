<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
require_once(dirname(__FILE__) . '/current.php');

need_manager();
need_auth('team');

$id = abs(intval($_GET['id']));
$team = $eteam = Table::Fetch('team', $id);

if ( is_get() && empty($team) ) {
	redirect( WEB_ROOT . '/manage/team/edit.php' );
}
else if ( is_post() ) {
	$team = $_POST;
	$insert = array(
			'sort_order',
			'bonus',
			'card',
			'credit',
			'farefree'
			);
	$table = new Table('team', $team);
	if ( $team['id'] && $team['id'] == $id ) {
		$table->SetPk('id', $id);
		$table->update($insert);
		Session::Set('notice', '编辑项目杂项信息成功');
		redirect( WEB_ROOT . "/manage/team/editzz.php?id={$id}");
	} 
	else {
		Session::Set('error', '编辑项目杂项信息失败');
		redirect(null);
	}
}

$selector = 'edit';
include template('manage_team_editzz');
