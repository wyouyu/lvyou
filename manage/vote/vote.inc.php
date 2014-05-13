<?php
function mcurrent_vote($selector='index'){
	$a = array(
		'/manage/vote/index.php' => '首页',
		'/manage/vote/feedback.php' => '反馈',
		'/manage/vote/question.php' => '问题',
	);
	$l = "/manage/vote/{$selector}.php";
	return current_link($l,$a,true);
}
