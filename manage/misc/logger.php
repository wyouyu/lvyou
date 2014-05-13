<?php
/***
 * 管理员操作日志
 * @author c
 * @data 2011-2-19
 * @file logger.php
 ***/

require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager(true);

/* 清除日志 */
$clear_data = strval($_POST['clear_data']);
if($clear_data){
	DB::Query('TRUNCATE TABLE `logger_admin`;');
	log_admin('system', '清空管理员操作日志');
}

$search = strval($_GET['search']);
$type = strval($_GET['type']);

$condition = array();

/* filter */
if ($search) { 
	$t_con['OR'] = array(
		"id like '%".mysql_escape_string($search)."%'",
		"email like '%".mysql_escape_string($search)."%'",
	);

	$teams = DB::LimitQuery('user', array(
				'condition' => $t_con,
				'one'=>true,
				));
	$condition['user_id'] = $teams['id'];
}
if ($type) $condition['type'] = $type;
/* end filter */

$count = Table::Count('logger_admin', $condition);
list($pagesize, $offset, $pagestring) = pagestring($count, 20);

$logs = DB::LimitQuery('logger_admin', array(
	'condition' => $condition,
	'order' => 'ORDER BY id DESC',
	'size' => $pagesize,
	'offset' => $offset,
));

$option_logger = array(
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
		);

include template('manage_misc_logger');
