<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();		//判断管理权限
need_auth('admin');

$daytime = strtotime(date('Y-m-d'));

require_once('vote.inc.php');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'question_list';

//列表
if ($action == 'list') {
	$count = Table::Count('vote_feedback');
	list($pagesize, $offset, $pagestring) = pagestring($count, 20);

	$feedback_list = DB::LimitQuery('vote_feedback', array(
		'order' => 'ORDER BY `id` DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));

	include template('manage_vote_feedback_list');
	exit;

//查看
} elseif ($action == 'view') {

	$id = isset($_REQUEST['id'])&&is_numeric($_REQUEST['id']) ? $_REQUEST['id'] : 0;
	$feedback = Table::Fetch('vote_feedback', $id);
	if (!$feedback) {
		Session::Set('error', '此调查反馈不存在。');
		redirect( WEB_ROOT . '/manage/vote/feedback.php');	
		exit;
	}

	$question_list = DB::LimitQuery('vote_question', array(
		'order' => 'ORDER BY `order` , id',
		'size' => 100,
		'offset' => $offset,
	));

	foreach($question_list AS $key=>$question) {
		$options_list = DB::LimitQuery('vote_options', array(
			'condition' => array(
					"`question_id` = '{$question['id']}'",
					"`is_show` = '1'"
				),
			'order' => 'ORDER BY `order` , id',
			'size' => 100,
			'offset' => $offset,
		));
		if (is_array($feedback_question_list)) {
			foreach($options_list AS $one) {
				$options_list_new[$one['id']] = $one;
			}
			$options_list = $options_list_new;
		}
		$question_list[$key]['options_list'] = $options_list;
	}

	$feedback_question_list = DB::LimitQuery('vote_feedback_question', array(
		'condition' => array("`feedback_id` = '{$feedback['id']}'"),
		'order' => 'ORDER BY id',
		'size' => 100,
		'offset' => $offset,
	));
	if (is_array($feedback_question_list)) {
		foreach($feedback_question_list AS $one) {
			$feedback_question_list_new[$one['question_id']][$one['options_id']] = $one;
		}
		$feedback_question_list = $feedback_question_list_new;
	}

	$feedback_input_list = DB::LimitQuery('vote_feedback_input', array(
		'condition' => array("`feedback_id` = '{$feedback['id']}'"),
		'order' => 'ORDER BY id',
		'size' => 100,
		'offset' => $offset,
	));
	if (is_array($feedback_input_list)) {
		foreach($feedback_input_list AS $one) {
			$feedback_input_list_new[$one['options_id']] = $one;
		}
		$feedback_input_list = $feedback_input_list_new;
	}


	include template('manage_vote_feedback_view');
	exit;


//删除
} elseif ($action == 'del') {
	$feedback_id = isset($_GET['id']) ? $_GET['id'] : '0';

	$flag_feedback = Table::Delete('vote_feedback', $feedback_id, 'id');
	$flag_feedback_question = Table::Delete('vote_feedback_question', $feedback_id, 'feedback_id');
	$flag_feedback_input = Table::Delete('vote_feedback_input', $feedback_id, 'feedback_id');
	if ($flag_feedback && $flag_feedback_question && $flag_feedback_input) {
		Session::Set('notice', '删除成功');
	} else {
		Session::Set('error', '删除失败');
	}

	redirect( WEB_ROOT . '/manage/vote/feedback.php?action=list');	
	exit;


//问题列表
} elseif ($action == 'question_list') {
	$show_all = isset($_GET['show_all'])&&$_GET['show_all'] ? true : false;

	$condition = array();
	if (!$show_all) { 
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

	foreach($question_list AS $key=>$question) {
		$sql = "SELECT * FROM `vote_feedback_question`
				WHERE `question_id` = '{$question['id']}'
				GROUP BY `feedback_id`";
		$feedback = DB::GetQueryResult($sql, 0);
		$question_list[$key]['feedback'] = count($feedback);
	}

	include template('manage_vote_feedback_question_list');
	exit;


//问题　详情
} elseif ($action == 'question_view') {
	$question_id = isset($_REQUEST['question_id'])&&is_numeric($_REQUEST['question_id']) ? $_REQUEST['question_id'] : 0;
	$question = Table::Fetch('vote_question', $question_id);
	if (!$question) {
		Session::Set('error', '此问题不存在。');
		redirect( WEB_ROOT . '/manage/vote/feedback.php');	
		exit;
	}

	$options_list = DB::LimitQuery('vote_options', array(
		'condition' => array(
				"`question_id` = '{$question['id']}'",
				"`is_show` = '1'"
			),
		'order' => 'ORDER BY `order` , id',
		'size' => 100,
		'offset' => $offset,
	));
	if (is_array($feedback_question_list)) {
		foreach($options_list AS $one) {
			$options_list_new[$one['id']] = $one;
		}
		$options_list = $options_list_new;
	}

	foreach($options_list AS $key=>$options) {
		$sql = "SELECT * FROM `vote_feedback_question`
				WHERE `question_id` = '{$question['id']}' AND `options_id`='{$options['id']}'
				GROUP BY `feedback_id`";
		$feedback = DB::GetQueryResult($sql, 0);
		$options_list[$key]['feedback'] = count($feedback);
	}

	include template('manage_vote_feedback_question_view');
	exit;


//查看自定义输入
} elseif ($action == 'input_list') {
	$options_id = isset($_REQUEST['options_id'])&&is_numeric($_REQUEST['options_id']) ? $_REQUEST['options_id'] : 0;

	$options = Table::Fetch('vote_options', $options_id);
	if (!$options) {
		Session::Set('error', '此选项不存在。');
		redirect( WEB_ROOT . '/manage/vote/feedback.php');	
		exit;
	}

	$question = Table::Fetch('vote_question', $options['question_id']);
	if (!$question) {
		Session::Set('error', '此问题不存在。');
		redirect( WEB_ROOT . '/manage/vote/feedback.php');	
		exit;
	}

	$sql = "SELECT *, COUNT(id) AS num FROM `vote_feedback_input`
			WHERE `options_id`='{$options['id']}'
			GROUP BY `value`
			ORDER BY `num` DESC
			";
	$input_list = DB::GetQueryResult($sql, 0);

	include template('manage_vote_feedback_input_list');
	exit;
}


redirect( WEB_ROOT . '/manage/vote/feedback.php');
exit;
