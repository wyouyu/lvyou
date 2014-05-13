<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
require_once(dirname(__FILE__) . '/current.php');

need_manager();
need_auth('team');

$id = abs(intval($_GET['id']));
$team = $eteam = Table::Fetch('team', $id);

if ( is_get() && empty($team) ) {
	redirect( WEB_ROOT . '/manage/team/edit.php' );
}
else if ( is_post() ) {
	$content = file_get_contents($_FILES['upload_txt']['tmp_name']);
	$lines = preg_split('/([\r\n])+/', $content, -1, PREG_SPLIT_NO_EMPTY);
	foreach($lines AS $line) {
		$codes = preg_split('/([,;\s])+/', $line, -1, PREG_SPLIT_NO_EMPTY);
		$codeone = strval($codes[0]);
		if ($codeone) ZVoucher::Create($codeone, $id);
	}
	$condition = array('team_id' => $id, );
	$count = Table::Count('voucher', $condition);
	Table::UpdateCache('team', $id, array('max_number' => $count,));

	Session::Set('notice', '上传商户券信息成功');
	redirect( WEB_ROOT . "/manage/team/editvoucher.php?id={$id}");
}

/* voucher list */
$condition = array('team_id' => $id, );
$count = Table::Count('voucher', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 50);

$vouchers = DB::LimitQuery('voucher', array(
	'condition' => $condition,
	'order' => 'ORDER BY order_id DESC, id ASC',
	'size' => $pagesize,
	'offset' => $offset,
));

$users = Table::Fetch('user', Utility::GetColumn($vouchers, 'user_id'));

$selector = 'edit';
include template('manage_team_editvoucher');
