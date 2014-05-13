<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$time_now = time();
$daytime = strtotime(date('Y-m-d'));
$ip = Utility::GetRemoteIp();

$vote_today = Table::Count('vote_feedback', array(
	'ip' => $ip,
	'user_id' => $login_user_id,
	"addtime > {$daytime}",
));
if ($vote_today) {
	Session::Set('notice', '您今天已经参加过调查。');
	redirect(WEB_ROOT . '/vote/index.php');
}


$question_list = DB::LimitQuery('vote_question', array(
			'condition' => array( 'is_show' => 1, ),
			'order' => 'ORDER BY `order` , id',
			'size' => 100,
			'offset' => $offset,
			));

$question_ids = join(',', Utility::GetColumn($question_list, 'id'));

$options_list = DB::LimitQuery('vote_options', array(
			'condition' => array(
				'question_id' => $question_ids,
				'is_show' => 1,
				),
			'order' => 'ORDER BY id',
			'size' => 100,
			'offset' => $offset,
			));

if (is_array($options_list)) {
	foreach($options_list AS $options_key=>$options) {
		$options_list_new[$options['id']] = $options;
	}
}
$options_list = $options_list_new;

$vote_list = array();
$input_list = array();
foreach($question_list AS $question) {
	$vote_options_list = isset($_POST['vote' . $question['id']]) ? $_POST['vote' . $question['id']] : '';
	if (!is_array($vote_options_list)) {
		continue;
	}
	foreach($vote_options_list AS $vote_options) {
		if (empty($options_list[$vote_options])) {
		} elseif ($options_list[$vote_options]['is_input']) {
			$input_value = isset($_POST['input' . $vote_options]) ? addslashes(htmlspecialchars($_POST['input' . $vote_options])) : '';
			if ($input_value) {
				$input_list_key = count($input_list);
				$input_list[$input_list_key] = array();
				$input_list[$input_list_key]['options_id'] = $vote_options;
				$input_list[$input_list_key]['value'] = $input_value;
			}
		}
		$vote_list_key = count($vote_list);
		$vote_list[$vote_list_key] =  array();
		$vote_list[$vote_list_key]['question_id'] = $question['id'];
		$vote_list[$vote_list_key]['options_id'] = $vote_options;
	}
}

if (!count($vote_list)) {
	Session::Set('error', '内容不能为空。');
	redirect( WEB_ROOT . '/vote/index.php');	
}

$feedback['user_id'] = isset($login_user['id']) ? $login_user['id'] : 0;
$feedback['username'] = isset($login_user['username']) ? $login_user['username'] : '';
$feedback['ip'] = $ip;
$feedback['addtime'] = $time_now;
$table_feedback = new Table('vote_feedback', $feedback);
$feedback_id = $table_feedback->insert(array('user_id', 'username', 'ip', 'addtime'));
if (!$feedback_id) {
	Session::Set('error', '添加失败，数据库操作失败。');
	redirect( WEB_ROOT . '/vote/index.php');	
}

foreach($vote_list AS $vote) {
	$vote['feedback_id'] = $feedback_id;
	$table_vote = new Table('vote_feedback_question', $vote);
	$table_vote->insert(array('feedback_id', 'question_id', 'options_id'));
}


foreach($input_list AS $input) {
	$input['feedback_id'] = $feedback_id;
	$table_input = new Table('vote_feedback_input', $input);
	$flag = $table_input->insert(array('feedback_id', 'options_id', 'value'));
}

if (is_post()) {
	Session::Set('notice', '提交数据成功，感谢您的参与。');
}
redirect( WEB_ROOT . '/vote/index.php');	
