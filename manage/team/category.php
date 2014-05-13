<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('team');

$cid = $_GET["cateid"];

$condition = array(
	'zone' => 'group',
	'display' => 'Y',
);
if ($cid) { 
	$condition[] = " fid = ".$cid ;
}
$cate = DB::LimitQuery('category', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
));
if(!$cate) die('<option value="0">暂无细分类</option>');
$cates = Utility::OptionArray($cate, 'id', 'name');
die (Utility::Option($cates));


