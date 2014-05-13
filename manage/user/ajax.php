<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('admin');

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));
$opuser = Table::Fetch('user', $id);

if ( 'delete' == $action ) {
	if ('Y'==$opuser['manager']) json('不能删除管理员用户', 'alert');
	if (0<$opuser['money']) json('用户余额大于0，不能删除', 'alert');
	$corder = Table::Count('order', array(
		'user_id' => $id,
		'state' => 'pay',
	));
	if (0<$corder) json('用户已经产生付款订单，不能删除', 'alert');
	Table::Delete('user', $id);
	Table::Delete('order', $id, 'user_id');
	Table::Delete('flow', $id, 'user_id');
	Table::Delete('coupon', $id, 'user_id');
	Table::Delete('ask', $id, 'user_id');
	Table::Delete('topic', $id, 'user_id');
        Table::Delete('toolsbind', $id, 'user_id');
	Session::Set('notice', '删除用户成功');
	json(null, 'refresh');
}
else if ( 'consume' == $action ) {
	$o_con = array(
		'user_id' => $id,
		'state' => 'pay',
	);
	$corders = DB::LimitQuery('order', array(
		'condition' => $o_con,
		'order' => 'ORDER BY id DESC',
	));
	$teams = Table::Fetch('team', Utility::GetColumn($corders, 'team_id'));

	$html = render('manage_user_ajax_consume');
	json($html, 'dialog');
}
else if ( 'flow' == $action ) {
	$condition = array( 'user_id' => $id,);
	$flows = DB::LimitQuery('flow', array(
		'condition'=>$condition,
		'order' => 'ORDER BY id DESC',
	));
	$html = render('manage_user_ajax_flow');
	json($html, 'dialog');
}
