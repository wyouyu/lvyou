<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('super');

if (is_post()) {
	if ($_POST['action'] == 'authorization') {
		$INI['authorization'][$_POST['id']] = $_POST['auth'];
		$admins = Table::Fetch('user', array('Y'), 'manager');
		$admin_ids = Utility::GetColumn($admins, 'id');
		foreach($INI['authorization'] AS $aid=>$aon) {
			if(!in_array($aid, $admin_ids)) {
				unset($INI['authorization'][$aid]);
			}
		}
		save_config();
		save_system($INI);
	}
	Session::Set('notice', '管理员授权成功');
	redirect($_SERVER['HTTP_REFERERE']);
}

/* build condition */
$condition = array('manager'=>'Y',);
$count = Table::Count('user', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$users = DB::LimitQuery('user', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));

include template('manage_user_manager');
