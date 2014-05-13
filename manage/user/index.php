<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$like = strval($_GET['like']);
$uname = strval($_GET['uname']);
$ucity = abs(intval($_GET['ucity']));
$numbers = abs(intval($_GET['numbers']));
$prices = abs(intval($_GET['prices']));
$havemoney = abs(intval($_GET['havemoney']));
$cs = strval($_GET['cs']);

$condition = array();

/* filter */
if ($like) { 
	$condition[] = "email like '%".mysql_escape_string($like)."%'";
}
if ($uname) {
	$condition[] = "username like '%".mysql_escape_string($uname)."%'";
}
if (abs(intval($ucity))) {
	$condition['city_id'] = abs(intval($ucity));

}
if ($numbers) {

    /* all user id */
	$u_sql = "SELECT user_id, count(user_id) AS count FROM `order` WHERE state = 'pay' GROUP BY user_id HAVING count > {$numbers}";
	$user_res = DB::GetQueryResult($u_sql, false);
	$user_ids = Utility::GetColumn($user_res, 'user_id');
	$user_ids = array_unique($user_ids);
	$uids = implode(',', $user_ids);
	$condition[] = "(id in($uids))";
}
if ($prices) {
    /* all user id */
	$p_sql = "SELECT user_id, sum(origin) AS count FROM `order` WHERE state = 'pay' GROUP BY user_id HAVING count > {$prices}";
	$p_res = DB::GetQueryResult($p_sql, false);
	$p_ids = Utility::GetColumn($p_res, 'user_id');
	$p_ids = array_unique($p_ids);
	$pids = implode(',', $p_ids);
	$condition[] = "(id in($pids))";
}
if ($havemoney) {
    /* have money */
	$condition[] = "money > {$havemoney}";
}
/* end */

$count = Table::Count('user', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$users = DB::LimitQuery('user', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));

include template('manage_user_index');

