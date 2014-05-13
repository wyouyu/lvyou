<?php
/***
 * 上传快递单号
 * @author c
 * @data 2011-2-18
 * @file express.php
 ***/

require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
need_auth('order');

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	$file = $_FILES['upload_express'];
	if ($file && strpos($file['type'], 'text')===0 && $file['error']==0) {
		$content = file($file['tmp_name']);
		$result = array();
		foreach ($content as $k=>$v) {
			$value = preg_split('/[\s,;]/', $v);
			$data['order_id'] = $value[0];
			$data['express_no'] = $value[1];
			$data['express_id'] = get_express_id($value[2]);
			update_order($data);
			unset($data);
		}
		Session::Set('notice', '数据更新成功');
	} 
}

/***
 * 根据快递公司名 获取快递公司分类id 或直接返回快递公司id
 * @param string $val 快递公司名 或 快递公司id
 * @return string $order['id'] 快递公司id
 ***/
function get_express_id($val) {
	/* 快递公司id为数字，依此判断直接返回或查询 */
	if (is_numeric($val)) {
		return $val;
	}
	$condition = array();
	$condition['name'] = $val; 
	$condition['zone'] = 'express'; 
	//DB::Debug();
	$order = DB::LimitQuery('category', array(
		'condition' => $condition,
		'select' => 'id',
		'one' => true,
	));
	//dbx($order);	//TODO:debug
	return $order['id'];
}

/***
 * 更新订单信息
 * @param array $data 更新数据
 * @return bool true or false
 ***/
function update_order($data){
	if(!isset($data['order_id']) || !isset($data['express_no']) || !isset($data['express_id'])){
		return false;
	}
	$id = (int) $data['order_id'];
	$express_no = $data['express_no'];
	$express_id = $data['express_id'];
	
	$order = DB::Update('order', array('id' => $id), array(
			'express_no' => $express_no,
			'express_id' => $express_id,
	));
}

include template('manage_order_express');