<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$iuser = strval($_GET['iuser']);
$puser = strval($_GET['puser']);
$iday = strval($_GET['iday']);
$pday = strval($_GET['pday']);
$tid = strval($_GET['tid']);

($s = strtolower(strval($_GET['s']))) || ($s = 'index');
if(!$s||!in_array($s, array('index', 'record', 'cancel'))) $s = 'index';

$condition = array( 'credit >= 0', 'pay' => 'N', 'buy_time > 0');
if('record'==$s) $condition['pay'] = 'Y';
if('cancel'==$s) $condition['pay'] = 'C';

/* filter */
if ($iuser) {
	$field = strpos($iuser, '@') ? 'email' : 'username';
	$field = is_numeric($iuser) ? 'id' : $field;
	$iuser_u = Table::Fetch('user', $iuser, $field);
	if($iuser_u) $condition['user_id'] = $iuser_u['id'];
	else $iuser= null;
}
if ($puser) {
	$field = strpos($puser, '@') ? 'email' : 'username';
	$field = is_numeric($puser) ? 'id' : $field;
	$puser_u = Table::Fetch('user', $puser, $field);
	if($puser_u) $condition['other_user_id'] = $puser_u['id'];
	else $puser= null;
}
if ($tid) {
	$condition['team_id'] = $tid;
}
if ($iday) { 
	$condition[] = "left(from_unixtime(create_time),10) = '".mysql_escape_string($iday)."'"; 
}
if ($pday) { 
	$condition[] = "left(from_unixtime(buy_time),10) = '".mysql_escape_string($pday)."'"; 
}
/* filter end */

$count = Table::Count('invite', $condition);
$summary = Table::Count('invite', $condition, 'credit');
list($pagesize, $offset, $pagestring) = pagestring($count, 20);
$invites = DB::LimitQuery('invite', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));

$team_ids = Utility::GetColumn($invites, 'team_id');
$teams = Table::Fetch('team', $team_ids);

$user_ids = Utility::GetColumn($invites, 'user_id');
$user_ido = Utility::GetColumn($invites, 'other_user_id');
$admin_ids = Utility::GetColumn($invites, 'admin_id');
$user_ids = array_merge($user_ids, $user_ido, $admin_ids);
$users = Table::Fetch('user', $user_ids);

include template('manage_misc_invite');
