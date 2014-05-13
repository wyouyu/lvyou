<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));

if ( 'edit' == $action ) {
	if ($id) {
		$goods = Table::Fetch('goods', $id);
		if (!$goods) json('无数据', 'alert');
	}
	$html = render('manage_ajax_dialog_goodsedit');
	json($html, 'dialog');
}
elseif ( 'remove' == $action ) {
	$goods = Table::Fetch('goods', $id);
	if (!$goods) json('无数据', 'alert');
	Table::Delete('goods', $id);
	Session::Set('notice', '删除商品成功');
	json(null, 'refresh');
}
elseif ( 'disable' == $action ) {
	$goods = Table::Fetch('goods', $id);
	if (!$goods) json('无数据', 'alert');
	$enable = ($goods['enable'] == 'Y') ? 'N' : 'Y';
	$enablestring = ($goods['enable']=='Y') ? '禁用' : '启用';
	Table::UpdateCache('goods', $id, array(
		'enable' => $enable,
	));
	Session::Set('notice', "{$enablestring}兑换商品成功");
	json(null, 'refresh');
}
elseif ( 'view' == $action ) {
  	$credit = Table::Fetch('credit', $id);
        $goods_id = Utility::GetColumn($credit, 'detail_id');
        $goods = Table::Fetch('goods', $goods_id);
	$html = render('manage_ajax_dialog_creditview');
	json($html, 'dialog');
}
elseif ( 'checkexpress' == $action ) {
	$credit = Table::Fetch('credit', $id);
	$html = render('ajax_dialog_checkexpress');
	json($html, 'dialog');
}
elseif ( 'editexpress' == $action ) {
	$u = array(
	     'state' => strval($_GET['s']),
	);
	if (!$credit['send_time']) {
	     $u['send_time'] = time();
	}
	Table::UpdateCache('credit', $id, $u);
        json( array(
		    array('data'=>'修改成功', 'type' => 'alert',),
		    array('data'=>'X.boxClose();', 'type' => 'eval',),
		    array('data'=>'null', 'type' => 'refresh',),
		     ), 'mix');

}
