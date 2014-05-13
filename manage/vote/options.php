<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();		//判断管理权限
need_auth('admin');

$daytime = strtotime(date('Y-m-d'));

require_once('vote.inc.php');

$pagesize = 100;

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

$question_id = isset($_REQUEST['question_id']) ? $_REQUEST['question_id'] : 0;
$question = Table::Fetch('vote_question', $question_id);
if (!$question) {
	Session::Set('error', '此问题不存在，请先添加。');
	redirect( WEB_ROOT . '/manage/vote/question.php?action=add');	
	exit;
}

//列表
if ($action == 'list') {

	$options_list = DB::LimitQuery('vote_options', array(
		'condition' => array("`question_id` = '{$question['id']}'"),
		'order' => 'ORDER BY `order` ASC',
		'size' => $pagesize,
		'offset' => $offset,
	));

	include template('manage_vote_options_list');
	exit;


//添加新选项
} elseif ($action == 'add') {
	$options['is_show'] = 1;
	$options['order'] = 0;
	include template('manage_vote_options_edit');
	exit;


//添加新选项，数据处理
} elseif ($action == 'add_submit') {
	$options['question_id'] = $question['id'];
	$options['name'] = isset($_POST['options']['name']) ? $_POST['options']['name'] : '';
	$options['is_br'] = isset($_POST['options']['is_br']) && $_POST['options']['is_br'] ? 1 : 0;
	$options['is_input'] = isset($_POST['options']['is_input']) && $_POST['options']['is_input'] ? 1 : 0;
	$options['is_show'] = isset($_POST['options']['is_show']) && $_POST['options']['is_show'] ? 1 : 0;
	$options['order'] = isset($_POST['options']['order'])&&is_numeric($_POST['options']['order']) ? $_POST['options']['order'] : '0';

	if (empty($options['name'])) {
		Session::Set('error', '选项名称不能为空。');
		redirect( WEB_ROOT . '/manage/vote/options.php?action=add&question_id=' . $question['id']);	
		exit;
	}

	$table = new Table('vote_options', $options);

	$name_check = Table::Count('vote_options', array(
		"`question_id` = '{$question['id']}'",
		"`name` = '{$options['name']}'",
	));
	if ($name_check) {
		Session::Set('error', '“'.$options['name'].'”已存在，请换一个选项名称。');
		redirect( WEB_ROOT . '/manage/vote/options.php?action=add&question_id=' . $question['id']);	
		exit;
	}

	$flag = $table->insert(array('question_id', 'name', 'is_br', 'is_input', 'is_show', 'order'));
	if ($flag) {
		Session::Set('notice', '添加选项成功');
	} else {
		Session::Set('notice', '添加选项失败');
	}
	redirect( WEB_ROOT . '/manage/vote/options.php?action=list&question_id=' . $question['id']);	
	exit;



//编辑
} elseif ($action == 'edit') {
	$id = isset($_GET['id'])&&is_numeric($_GET['id']) ? $_GET['id'] : 0;

	$options = Table::Fetch('vote_options', $id);
	if (!$options) {
		Session::Set('error', '此选项不存在，请先添加。');
		redirect( WEB_ROOT . '/manage/vote/options.php?action=add&question_id=' . $question['id']);	
		exit;
	}

	include template('manage_vote_options_edit');
	exit;


//编辑，提交数据
} elseif ($action == 'edit_submit') {
	$options['id'] = isset($_POST['options']['id']) ? $_POST['options']['id'] : '0';
	$options['question_id'] = $question['id'];
	$options['name'] = isset($_POST['options']['name']) ? $_POST['options']['name'] : '';
	$options['is_br'] = isset($_POST['options']['is_br']) && $_POST['options']['is_br'] ? 1 : 0;
	$options['is_input'] = isset($_POST['options']['is_input']) && $_POST['options']['is_input'] ? 1 : 0;
	$options['is_show'] = isset($_POST['options']['is_show']) && $_POST['options']['is_show'] ? 1 : 0;
	$options['order'] = isset($_POST['options']['order'])&&is_numeric($_POST['options']['order']) ? $_POST['options']['order'] : '0';

	$options_check = Table::Count('vote_options', array(
		"id = '{$options['id']}'",
	));
	if (!$options_check) {
		Session::Set('error', '此选项不存在，请先添加。');
		redirect( WEB_ROOT . '/manage/vote/options.php?action=add&question_id=' . $question_id);
		exit;
	}

	$name_check = Table::Count('vote_options', array(
		"id != '{$options['id']}' AND `name` = '{$options['name']}'",
	));
	if ($name_check) {
		Session::Set('error', '“'.$options['name'].'”已存在，请换一个标题。');
		redirect( WEB_ROOT . '/manage/vote/options.php?action=edit&question_id=' . $question['id'] . '&id='.$options['id']);	
		exit;
	}

	$table = new Table('vote_options', $options);
	$up_array = array('question_id', 'name', 'is_br', 'is_input', 'is_show', 'order');
	$flag = $table->update( $up_array );
	if ( $flag ) {
		Session::Set('notice', '修改选项成功');
	} else {
		Session::Set('error', '修改选项失败');
	}

	redirect( WEB_ROOT . '/manage/vote/options.php?action=edit&question_id=' . $question['id'] . '&id=' . $options['id']);	
	exit;




//更改状态隐藏
} elseif ($action == 'change_show') {
	$options['id'] = isset($_GET['id']) ? $_GET['id'] : '0';
	$options['is_show'] = isset($_GET['value'])&&$_GET['value'] ? 1 : 0;

	$options_check = Table::Count('vote_options', array(
		"id = '{$options['id']}'",
	));
	if (!$options_check) {
		Session::Set('error', '此选项不存在，请先添加。');
		redirect( WEB_ROOT . '/manage/vote/options.php?action=add&question_id=' . $question['id']);	
		exit;
	}

	$table = new Table('vote_options', $options);
	$up_array = array('is_show');
	$flag = $table->update( $up_array );
	if ( $flag ) {
		Session::Set('notice', '修改选项状态成功');
	} else {
		Session::Set('error', '修改选项状态失败');
	}

	redirect( WEB_ROOT . '/manage/vote/options.php?action=list&question_id=' . $question['id']);	
	exit;


//删除
} elseif ($action == 'del') {
	$options['id'] = isset($_GET['id']) ? $_GET['id'] : '0';

	$flag = Table::Delete('vote_options', $options['id']);
	if ( $flag ) {
		Session::Set('notice', '删除选项成功');
	} else {
		Session::Set('error', '删除选项失败');
	}

	redirect( WEB_ROOT . '/manage/vote/options.php?action=list&question_id=' . $question['id']);	
	exit;


}




redirect( WEB_ROOT . '/manage/vote/options.php?action=list&question_id=' . $question_id);
exit;
