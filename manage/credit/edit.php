<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('market');

$id = abs(intval($_REQUEST['id']));
$goods = Table::Fetch('goods', $id);

$table = new Table('goods', $_POST);
$table->letter = strtoupper($table->letter);
$uarray = array('title','number','image','score','per_number','display','sort_order','time'); 
$table->display = strtoupper($table->display)=='Y' ? 'Y' : 'N';
$table->image = upload_image('upload_image', $goods['image'], 'team');
$table->time = time();

if (!$_POST['title'] || !$_POST['score'] ) {
	Session::Set('error', '商品标题、兑换积分不能为空');
	redirect(null);
}

if ( $goods ) {
	if ( $flag = $table->update( $uarray ) ) {
		Session::Set('notice', '编辑商品成功');
	} else {
		Session::Set('error', '编辑商品失败');
	}
} else {
	if ( $flag = $table->insert( $uarray ) ) {
		Session::Set('notice', '新建商品成功');
	} else {
		Session::Set('error', '新建商品失败');
	}
}

redirect(null);
