<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();		//判断管理权限
need_auth('admin');

$daytime = strtotime(date('Y-m-d'));

require_once('vote.inc.php');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list-is_show-1';

//列表
if ($action == 'list-all' || $action == 'list-is_show-1') {
	$condition = array();
	if ($action == 'list-is_show-1') { 
		$condition[] = "`is_show` = 1";
	}

	$count = Table::Count('vote_question', $condition);
	list($pagesize, $offset, $pagestring) = pagestring($count, 20);

	$question_list = DB::LimitQuery('vote_question', array(
		'condition' => $condition,
		'order' => 'ORDER BY `order` ASC',
		'size' => $pagesize,
		'offset' => $offset,
	));

	include template('manage_vote_question_list');
	exit;

//更改状态隐藏
} elseif ($action == 'change_show') {
	$question['id'] = isset($_GET['id']) ? $_GET['id'] : '0';
	$question['is_show'] = isset($_GET['value'])&&$_GET['value'] ? 1 : 0;

	$question_check = Table::Count('vote_question', array(
		"id = '{$question['id']}'",
	));
	if (!$question_check) {
		Session::Set('error', '此问题不存在，请先添加。');
		redirect( WEB_ROOT . '/manage/vote/question.php?action=add');	
		exit;
	}

	$table = new Table('vote_question', $question);
	$up_array = array('is_show');
	$flag = $table->update( $up_array );
	if ( $flag ) {
		Session::Set('notice', '修改状态成功');
	} else {
		Session::Set('error', '修改状态失败');
	}

	redirect( WEB_ROOT . '/manage/vote/question.php?action=list-all');	
	exit;

//删除
} elseif ($action == 'del') {
	$question['id'] = isset($_GET['id']) ? $_GET['id'] : '0';

	$flag = Table::Delete('vote_question', $question['id']);
	if ( $flag ) {
		Session::Set('notice', '删除成功');
	} else {
		Session::Set('error', '删除失败');
	}

	redirect( WEB_ROOT . '/manage/vote/question.php?action=list-all');	
	exit;


//编辑问题
} elseif ($action == 'edit') {
	$id = isset($_GET['id'])&&is_numeric($_GET['id']) ? $_GET['id'] : 0;

	$question = Table::Fetch('vote_question', $id);
	if (!$question) {
		Session::Set('error', '此问题不存在，请先添加。');
		redirect( WEB_ROOT . '/manage/vote/question.php?action=add');	
		exit;
	}

	include template('manage_vote_question_edit');
	exit;


//编辑问题，提交数据
} elseif ($action == 'edit_submit') {
	$question['id'] = isset($_POST['question']['id']) ? $_POST['question']['id'] : '0';
	$question['title'] = isset($_POST['question']['title']) ? addslashes(htmlspecialchars($_POST['question']['title'])) : '';
	$question['type'] = isset($_POST['question']['type']) && $_POST['question']['type']=='radio' ? 'radio' : 'checkbox';
	$question['is_show'] = isset($_POST['question']['is_show']) && $_POST['question']['is_show'] ? 1 : 0;
	$question['order'] = isset($_POST['question']['order'])&&is_numeric($_POST['question']['order']) ? $_POST['question']['order'] : '0';

	$question_check = Table::Count('vote_question', array(
		"id = '{$question['id']}'",
	));
	if (!$question_check) {
		Session::Set('error', '此问题不存在，请先添加。');
		redirect( WEB_ROOT . '/manage/vote/question.php?action=add');	
		exit;
	}

	$title_check = Table::Count('vote_question', array(
		"id != '{$question['id']}' AND `title` = '{$question['title']}'",
	));
	if ($title_check) {
		Session::Set('error', '“'.$question['title'].'”已存在，请换一个标题。');
		redirect( WEB_ROOT . '/manage/vote/question.php?action=edit&id='.$question['id']);	
		exit;
	}

	$table = new Table('vote_question', $question);
	$up_array = array(
			'title', 'type', 'is_show', 'order',
			);
	$flag = $table->update( $up_array );
	if ( $flag ) {
		Session::Set('notice', '修改问题成功');
	} else {
		Session::Set('error', '修改问题失败');
	}

	redirect( WEB_ROOT . '/manage/vote/question.php?action=edit&id=' . $question['id']);	
	exit;


} elseif ($action == 'add') {
	$question['type'] = 'radio';
	$question['is_show'] = 1;
	$question['order'] = 0;
	include template('manage_vote_question_edit');
	exit;

//添加问题，数据处理
} elseif ($action == 'add_submit') {
	$question['title'] = isset($_POST['question']['title']) ? addslashes(htmlspecialchars($_POST['question']['title'])) : '';
	$question['type'] = isset($_POST['question']['type']) && $_POST['question']['type']=='radio' ? 'radio' : 'checkbox';
	$question['is_show'] = isset($_POST['question']['is_show']) && $_POST['question']['is_show'] ? 1 : 0;
	$question['order'] = isset($_POST['question']['order'])&&is_numeric($_POST['question']['order']) ? $_POST['question']['order'] : '0';

	$table = new Table('vote_question', $question);

	$title_check = Table::Count('vote_question', array(
		"title = '{$question['title']}'",
	));
	if ($title_check) {
		Session::Set('error', '“'.$question['title'].'”已存在，请换一个标题。');
		redirect( WEB_ROOT . '/manage/vote/question.php?action=add');	
		exit;
	}

	$table->addtime = time();
	$table->insert(array(
		'title', 'type', 'is_show', 'addtime', 'order',
	));

	Session::Set('notice', '添加调查问题成功');
	redirect( WEB_ROOT . '/manage/vote/question.php?action=list-all');	
	exit;
}

if ($action == 'add' || $action == 'edit') {
	include template('manage_vote_question_edit');
} else {
	include template('manage_vote_question_list');
}
