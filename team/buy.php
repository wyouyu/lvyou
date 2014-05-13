<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$id = abs(intval($_GET['id']));

$team = Table::Fetch('team', $id);
if ( !$team || $team['begin_time']>time() ) {
	Session::Set('error', '团购项目不存在');
	redirect( WEB_ROOT . '/index.php' );
}
//whether buy
$ex_con = array(
		'user_id' => $login_user_id,
		'team_id' => $team['id'],
		'state' => 'unpay',
		);
$order = DB::LimitQuery('order', array(
	'condition' => $ex_con,
	'one' => true,
));

//buyonce
if (strtoupper($team['buyonce'])=='Y') {
	$ex_con['state'] = 'pay';
	if ( Table::Count('order', $ex_con) ) {
		Session::Set('error', '您已经成功购买了本单产品，请勿重复购买，快去关注一下其他产品吧！');
		redirect( WEB_ROOT . "/team.php?id={$id}"); 
	}
}

//bind mobile can buy
if (option_yes('bindmobile')) {
	if(!$login_user_id){
	   Session::Set('error', '登录后绑定手机的用户才能参团,赶快登录吧！');
	   redirect( WEB_ROOT . "/team.php?id={$id}"); 
	}
	$condition = array( 
		      'enable' => 'Y',
		      'user_id' => $login_user_id,
		);
    $havebind = DB::GetTableRow ('toolsbind', $condition);
	if ( !$havebind ) {
		Session::Set('error', '绑定手机的用户才能参团,赶快在账户信息里绑定手机吧！');
		redirect( WEB_ROOT . "/team.php?id={$id}"); 
	}
}

//peruser buy count
if ($team['per_number']>0) {
	$now_count = Table::Count('order', array(
		'user_id' => $login_user_id,
		'team_id' => $id,
		'state' => 'pay',
	), 'quantity');
	$team['per_number'] -= $now_count;
	if ($team['per_number']<=0) {
		Session::Set('error', '您购买本单产品的数量已经达到上限，快去关注一下其他产品吧！');
		redirect( WEB_ROOT . "/team.php?id={$id}"); 
	}
}
else {
	if ($team['max_number']>0) $team['per_number'] = $team['max_number'] - $team['now_number'];
}

if ( is_get() ) {
		Session::Set('loginpage', $_SERVER['REQUEST_URI']);
	}
//post buy
if ( $_POST ) {
	//need_login();
	
	$condbuy = implode('@', $_POST['condbuy']);
	
	$table = new Table('order', $_POST);
	$table->quantity = abs(intval($table->quantity));

	if ( $table->quantity == 0 ) {
		Session::Set('error', '购买数量不能小于1份');
		redirect( WEB_ROOT . "/team/buy.php?id={$team['id']}");
	} 
	elseif ( $team['per_number']>0 && $table->quantity > $team['per_number'] ) {
		Session::Set('error', '您本次购买本单产品已超出限额！');
		redirect( WEB_ROOT . "/team.php?id={$id}"); 
	}

	if ($order && $order['state']=='unpay') {
		$table->SetPk('id', $order['id']);
	}

	$table->user_id = $login_user_id;
	$table->state = 'unpay';
	$table->allowrefund = $team['allowrefund'];
	$table->team_id = $team['id'];
	$table->city_id = $team['city_id'];
	$table->express = ($team['delivery']=='express') ? 'Y' : 'N';
	$table->fare = $table->express=='Y' ? $express_price : 0;
	$table->express_id = $table->express=='Y' ? $express_id : 0;
	$table->price = $team['team_price'];
	$table->credit = 0;
    $table->type = 4;
	$table->condbuy = $condbuy;
    
	if ( $table->id ) {
		$eorder = Table::Fetch('order', $table->id);
		if ($eorder['state']=='unpay' 
				&& $eorder['team_id'] == $id
				&& $eorder['user_id'] == $login_user_id
		   ) {
			$table->origin = team_origin($team, $table->quantity,$express_price);
			$table->origin -= $eorder['card'];
		} else {
			$eorder = null;
		}
	}
	if (!$eorder){
		$table->pk_value='';
		$table->create_time = time();
		$table->origin = team_origin($team, $table->quantity,$express_price);
        $table->money = $table->origin;
	}

	$insert = array(
			'user_id', 'team_id', 'city_id', 'state', 'express_id',
			'fare', 'express_xx','express', 'origin', 'price',
			'address', 'zipcode', 'realname', 'mobile', 
			'quantity', 'create_time', 'remark', 'condbuy','type','money'
		);
	if($team['allowrefund']=='Y') $insert[] = 'allowrefund' ;
	if ($flag = $table->update($insert)) {
		$order_id = abs(intval($table->id));
		
		/* 插入订单来源 */
		$data['order_id'] = $order_id;
		$data['user_id'] = $login_user_id;
		$data['referer'] = $_COOKIE['referer'];
		$data['create_time'] = time();
		DB::Insert('referer', $data);
		
		redirect(WEB_ROOT."/order/tcheck.php?id={$order_id}");
	}
}
//each user per day per buy
if (!$order) { 
	$order = json_decode(Session::Get('loginpagepost'),true);
	settype($order, 'array');
	if ($order['mobile']) $login_user['mobile'] = $order['mobile'];
	if ($order['zipcode']) $login_user['zipcode'] = $order['zipcode'];
	if ($order['address']) $login_user['address'] = $order['address'];
	if ($order['realname']) $login_user['realname'] = $order['realname'];
        if ($team['permin_number']>1 && ($team['permin_number']<$team['per_number']|| $team['per_number']==0)){
        $order['quantity'] = $team['permin_number'];
        }else{
        $order['quantity'] = 1;
        }
}
//end;
$order['origin'] = team_origin($team, $order['quantity']);

if ($team['max_number']>0 && $team['conduser']=='N') {
	$left = $team['max_number'] - $team['now_number'];
	if ($team['per_number']>0) {
		$team['per_number'] = min($team['per_number'], $left);
	} else {
		$team['per_number'] = $left;
	}
}

$team['condbuy'] = explode('@', $team['condbuy']);

foreach ($team['condbuy'] as $k=>$v) {
	$team['condbuy'][$k] = nanooption($v);
}


include template('team_buy');
