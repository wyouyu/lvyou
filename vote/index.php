<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

//今日接受调查人次
$daytime = strtotime(date('Y-m-d'));
$ip = Utility::GetRemoteIp();

$action = $_GET['action'] ? $_GET['action'] : '';
if ($action == 'addSuccess') {
	Session::Set('notice', '提交数据成功，感谢您的参与。');
	redirect( WEB_ROOT . '/vote/index.php' );
}

$question_list = DB::LimitQuery('vote_question', array(
	'condition' => array("`is_show` = '1'"),
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
	$question_list[$key]['options_list'] = $options_list;
}

$pagetitle = '用户调查';
include template('vote_index');
