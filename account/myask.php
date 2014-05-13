<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();

$pagetitle = "我的问答";
$condition = array( 'length(comment)>0' );

$condition['user_id'] = $login_user_id;

/*pageit*/
$count = Table::Count('ask', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 10);
$asks = DB::LimitQuery('ask', array(
			'condition' => $condition,
			'order' => 'ORDER BY id DESC',
			'size' => $pagesize,
			'offset' => $offset,
			));
/*endpage*/

include template('account_myask');
