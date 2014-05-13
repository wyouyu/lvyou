<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_manager();

$action = strval($_GET['action']);
$id = $topic_id = abs(intval($_GET['id']));
$topic = Table::Fetch('topic', $id);
$pid = abs(intval($topic['parent_id']));

if (!$topic || !$id) {
	json('话题不存在', 'alert');
}
elseif ( $action == 'topicremove') {
	if ( $pid==0 ) {
		Table::Delete('topic', $id);
		Table::Delete('topic', $id, 'parent_id');
	} else {
		Table::Delete('topic', $id);
		Table::UpdateCache('topic', $pid, array(
			'reply_number' => Table::Count('topic', array('parent_id' => $pid) ),
		));
	}
	Session::Set('notice', '删除帖子成功');
	json(null, 'refresh');
}
elseif ( $action == 'topichead' ) {
	if ( $topic['parent_id']>0 ) {
		json('只有主话题才能置顶', 'alert');
	}
	$head = ($topic['head']==0) ? time() : 0;
	Table::UpdateCache('topic', $id, array( 'head' => $head,));
	$tip = $head ? '设置话题置顶成功' : '取消话题置顶成功';
	Session::Set('notice', $tip);
	json(null, 'refresh');
}
