<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();		//判断管理权限
need_auth('admin');

$daytime = strtotime(date('Y-m-d'));

require_once('vote.inc.php');

//今日接受调查人次
$vote_feedback_today_count = Table::Count('vote_feedback', array(
	"addtime > {$daytime}",
));

//全部接受调查人次
$vote_feedback_all_count = Table::Count('vote_feedback');

//正在调查问题数
$vote_question_show_count =  Table::Count('vote_question', array(
	"is_show = 1",
));

//全部调查问题数
$vote_question_all_count = Table::Count('vote_question');

include template('manage_vote_index');
