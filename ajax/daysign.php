<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');


$action = strval($_GET['action']);
$user_id =  abs(intval($_GET['id']));

if (!$user_id) {
	json('请登录后在签到', 'alert');
}

if ( $action == 'daily' ) {
	 $daytime = strtotime(date('Y-m-d'));
	 $condition = array( 
		      'user_id' => $user_id,
		      'create_time' => $daytime,
		);
	 $have = DB::GetTableRow('daysign', $condition);
	 if($have) json('您今天已经签到过了', 'alert');

     $money = abs(strval($INI['system']['givemoney']));
	 $credit = abs(strval($INI['system']['givecredit']));     
	 
     if(option_yes('givecredit')) ZCredit::Create($credit, $user_id, 'daysign');
     if(option_yes('givemoney'))
	 ZFlow::CreateFromDaysign($user_id, $money); 
     if($money=='0' && $credit == '0') json('暂未设置签到赠送值', 'alert'); 
	 $u = array(
				'user_id' => $user_id,
				'credit' => $credit,
				'money' => $money,
				'create_time' => $daytime,
				);
	 DB::Insert('daysign', $u);
	 json(array(
			array('data' => "签到成功", 'type'=>'alert'),
			array('data' => null, 'type'=>'refresh'),
			), 'mix');
}


