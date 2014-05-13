<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('team');

$id = abs(intval($_GET['id']));
$team = Table::Fetch('team', $id);
$order = Table::Fetch('order', $id, 'team_id');
if ( $order ) {
	Session::Set('notice', "删除团购({$id})记录失败，存在订单记录");
} else {
	Table::Delete('team', $id);
	Session::Set('notice', "删除团购({$id})记录成功");
}
redirect(udecode($_GET['r']));
