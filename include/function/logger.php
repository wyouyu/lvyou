<?php
/***
 * 管理员日志记录,记录到数据库
 * @author c
 * @data 2011-2-19
 * @file logger.php
 * 
 * 	记录类型
	'misc' => '首页',
	'team' => '项目',
	'order'=> '订单',
	'coupon'=>'优惠卷',
	'user'=>'用户',
	'partner'=>'商户',
	'market'=>'营销',
	'category'=>'类别',
	'vote'=>'调查',
	'credit'=>'积分',
	'system'=>'设置',
 * @param string $type 记录类型 
 * @param string $operation 具体操作信息
 * @param string $relate_data 相关操作数据	
 * @return mixed
 ***/
function log_admin($type, $operation , $relate_data = array()){
	global $login_user;
	$user_id = $login_user['id'];
	$user_email = $login_user['email'];
	$create_on = date('Y-m-d H:i:s');
	$relate_data = serialize($relate_data);
	
	DB::Insert('logger_admin', array(
		'user_id' => $user_id,
		'user_email' => $user_email,
		'type'=> $type,
		'operation' => $operation,
		'relate_data' => $relate_data,
		'create_on' => $create_on,
	));
}