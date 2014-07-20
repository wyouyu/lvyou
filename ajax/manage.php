<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_manager();

$action = strval($_GET['action']);
$id = abs(intval($_GET['id']));

if ( 'orderrefund' == $action) {
	need_auth('admin');
	$order = Table::Fetch('order', $id);
	$rid = strtolower(strval($_GET['rid']));
	if ( $rid == 'credit' ) {
		ZFlow::CreateFromRefund($order);
	} else {
		Table::UpdateCache('order', $id, array(
					'service' => 'cash',
                    'rstate' => 'berefund',
					'state' => 'unpay'
			));
	}
	/* team -- */
	$team = Table::Fetch('team', $order['team_id']);
	team_state($team);
	if ( $team['state'] != 'failure' ) {
		$minus = $team['conduser'] == 'Y' ? 1 : $order['quantity'];
		Table::UpdateCache('team', $team['id'], array(
					'now_number' => array( "now_number - {$minus}", ),
		));
	}
    /* credit */
	ZCredit::Refund($order['user_id'], $order);
	/* coupons */
	if ( in_array($team['delivery'], array('coupon', 'pickup') )) {
		$coupons = Table::Fetch('coupon', array($order['id']), 'order_id');
		foreach($coupons AS $one) Table::Delete('coupon', $one['id']);
	}else if ( $team['delivery'] == 'voucher') {
		DB::Update('voucher', array('order_id' => $order['id'],), array(
			'order_id' => 0,
			'user_id' => 0,
			'sms' => 0,
		));
	}

	/* order update */
	Table::UpdateCache('order', $id, array(
				'express_no' => '',
				));
    log_admin('order', '订单'.$order['id'].'退款');
	Session::Set('notice', '退款成功');
	json(null, 'refresh');
}
elseif ( 'orderremove' == $action) {
	need_auth('order');
	$order = Table::Fetch('order', $id);
	if ( $order['state'] != 'unpay' ) {
		json('付款订单不能删除', 'alert');
	}
	/* card refund */
	if ( $order['card_id'] ) {
		Table::UpdateCache('card', $order['card_id'], array(
			'consume' => 'N',
			'team_id' => 0,
			'order_id' => 0,
		));
	}
    log_admin('order', '删除订单'.$order['id']);
	Table::Delete('order', $order['id']);
	Session::Set('notice', "删除订单 {$order['id']} 成功");
	json(null, 'refresh');
}
elseif ( 'huiyishiremove' == $action) {
	//need_auth('order');
	$huiyishi = Table::Fetch('huiyishi', $id);
	
	Table::Delete('huiyishi', $huiyishi['id']);
	Session::Set('notice', "删除成功");
	json(null, 'refresh');
}
elseif ( 'tesecairemove' == $action) {
	//need_auth('order');
	//$huiyishi = Table::Fetch('tesecai', $id);
	
	Table::Delete('tesecai', $id);
	Session::Set('notice', "删除成功");
	json(null, 'refresh');
}
else if ( 'ordercash' == $action ) {
	need_auth('order');
	$order = Table::Fetch('order', $id);
	ZOrder::CashIt($order);
	$user = Table::Fetch('user', $order['user_id']);
	Session::Set('notice', "现金付款成功，购买用户：{$user['email']}");
	json(null, 'refresh');
}
else if ( 'teamdetail' == $action) {
	$team = Table::Fetch('team', $id);
	$partner = Table::Fetch('partner', $team['partner_id']);

	$paycount = Table::Count('order', array(
		'state' => 'pay',
		'team_id' => $id,
	));
	$buycount = Table::Count('order', array(
		'state' => 'pay',
		'team_id' => $id,
	), 'quantity');
	$onlinepay = Table::Count('order', array(
		'state' => 'pay',
		'team_id' => $id,
	), 'money');
	$creditpay = Table::Count('order', array(
		'state' => 'pay',
		'team_id' => $id,
	), 'credit');
	$cardpay = Table::Count('order', array(
		'state' => 'pay',
		'team_id' => $id,
	), 'card');
	$couponcount = Table::Count('coupon', array(
		'team_id' => $id,
	));
	$team['state'] = team_state($team);
	$city_id = abs(intval($team['city_id']));
	$subcond = array(); if($city_id) $subcond['city_id'] = $city_id;
	$subcount = Table::Count('subscribe', $subcond);
	$subcond['enable'] = 'Y';
	$smssubcount = Table::Count('smssubscribe', $subcond);

	/* send team subscribe mail */	
	$team['noticesubscribe'] = ($team['close_time']==0&&is_manager());
	$team['noticesmssubscribe'] = ($team['close_time']==0&&is_manager());
	/* send success coupon */
	$team['noticesms'] = ($team['delivery']!='express')&&(in_array($team['state'], array('success', 'soldout')))&&is_manager();
	/* teamcoupon */
	$team['teamcoupon'] = ($team['noticesms']&&$buycount>$couponcount);
	$team['needline'] = ($team['noticesms']||$team['noticesubscribe']||$team['teamcoupon']);

	/* 处理多城市id */
	$team['city_ids'] = array_filter(explode('@', $team['city_ids']));

	$html = render('manage_ajax_dialog_teamdetail');
	json($html, 'dialog');
}
else if ( 'teamremove' == $action) {
	need_auth('team');
	$team = Table::Fetch('team', $id);
	$order_count = Table::Count('order', array(
		'team_id' => $id,
		'state' => 'pay',
	));
	if ( $order_count > 0 ) {
		json('本团购包含付款订单，不能删除', 'alert');
	}
	ZTeam::DeleteTeam($id);

	/* remove coupon */
	$coupons = Table::Fetch('coupon', array($id), 'team_id');
	foreach($coupons AS $one) Table::Delete('coupon', $one['id']);
	/* remove order */
	$orders = Table::Fetch('order', array($id), 'team_id');
	foreach($orders AS $one) Table::Delete('order', $one['id']);
	/* end */

	Session::Set('notice', "团购 {$id} 删除成功！");
	json(null, 'refresh');
}
else if ( 'cardremove' == $action) {
	need_auth('market');
	$id = strval($_GET['id']);
	$card = Table::Fetch('card', $id);
	if (!$card) json('没有相关代金券', 'alert');
	if ($card['consume']=='Y') { json('代金券已经被使用，不能删除', 'alert'); }
	Table::Delete('card', $id);
	Session::Set('notice', "代金券 {$id} 删除成功！");
	json(null, 'refresh');
}
else if ( 'paycardremove' == $action) {
	need_auth('market');
	$id = strval($_GET['id']);
	$paycard = Table::Fetch('paycard', $id);
	if (!$paycard) json('没有相关充值卡', 'alert');
	if ($paycard['consume']=='Y') { json('充值卡已经被使用，不能删除', 'alert'); }
	Table::Delete('paycard', $id);
	Session::Set('notice', "充值卡 {$id} 删除成功！");
	json(null, 'refresh');
}
else if( 'ticketremove' ==$action)
{
    need_auth('market');
    $id = strval($_GET['id']);
    Table::Delete('ticket', $id);
    Session::Set('notice', "门票 {$id} 删除成功！");
    json(null, 'refresh');
}
else if ( 'cardremovezone' == $action) {
	need_auth('market');
	$id = strval($_GET['id']);
	$card = Table::Fetch('card', $id);
	if (!$card) json('没有相关代金券', 'alert');
	$c = array( 
			'consume' => 'N', 
			'code' => $card['code'], 
			'credit'=>$card['credit'],
			);
	$count = Table::Count('card', $c);
	DB::Delete('card', $c);
	Session::Set('notice', "同批次未使用代金券{$count}张删除成功！");
	json(null, 'refresh');
}
else if ( 'userview' == $action) {
	$user = Table::Fetch('user', $id);
	$user['costcount'] = Table::Count('order', array(
		'state' => 'pay',
		'user_id' => $id,
	));
	$user['cost_all'] = Table::Count('flow', array(
		'direction' => 'expense',
		'user_id' => $id,
	), 'money');
	$user['cost_refund'] = Table::Count('flow', array(
		'direction' => 'income',
		'action' => 'refund',
		'user_id' => $id,
	), 'money');
	$user['cost'] = $user['cost_all'] - $user['cost_refund'];
	$html = render('manage_ajax_dialog_user');
	json($html, 'dialog');
}
else if ( 'usermoney' == $action) {
	need_auth('admin');
	$user = Table::Fetch('user', $id);
	$money = moneyit($_GET['money']);
	if ( $money < 0 && $user['money'] + $money < 0) {
		json('提现失败 - 用户余额不足', 'alert');
	}
	if ( ZFlow::CreateFromStore($id, $money) ) {
		$action = ($money>0) ? '线下充值' : '用户提现';
		$money = abs($money);
        log_admin('user', $user['email'].$action.$money.'元');
		json(array(
					array('data' => "{$action}{$money}元成功", 'type'=>'alert'),
					array('data' => null, 'type'=>'refresh'),
				  ), 'mix');
	}
	json('充值失败', 'alert'); 
}
else if ( 'orderexpress' == $action ) {
	need_auth('order');
	$express_id = abs(intval($_GET['eid']));
	$express_no = strval($_GET['nid']);
	if (!$express_id) $express_no = null;
	Table::UpdateCache('order', $id, array(
		'express_id' => $express_id,
		'express_no' => $express_no,
	));
	json(array(
				array('data' => "修改快递信息成功", 'type'=>'alert'),
				array('data' => null, 'type'=>'refresh'),
			  ), 'mix');
}
else if ( 'orderview' == $action) {
	$order = Table::Fetch('order', $id);
	$user = Table::Fetch('user', $order['user_id']);
	$team = Table::Fetch('team', $order['team_id']);
    //find coupons
	$coupons = DB::LimitQuery('coupon', array(
			   'condition' => array(
		            'order_id' => $order['id'],
		            'user_id'=> $user['id'],
		            ),  
		));
	$order['referer'] = Table::Fetch('referer', $id, 'order_id');
	
	if ($team['delivery'] == 'express') {
		$option_express = option_category('express');
		$order['express_name'] = $option_express[$order['express_id']];
	}
	$payservice = array(
		'alipay' => '支付宝',
		'tenpay' => '财付通',
		'chinabank' => '网银在线',
		'credit' => '余额付款',
		'cash' => '线下支付',
        'yeepay' => '易宝',
		'sdopay' => '盛付通',
		'cmpay' => '手机支付',
		'paypal' => 'Paypal',
	);
	$paystate = array(
		'unpay' => '<font color="green">未付款</font>',
		'pay' => '<font color="red">已付款</font>',
	);
	$option_refund = array(
		'credit' => '退款到账户余额',
		'online' => '其他途径已退款',
	);
	
	$html = render('manage_ajax_dialog_orderview');
	json($html, 'dialog');
}
else if ( 'torderview' == $action) {
    $order = Table::Fetch('order', $id);
    $user = Table::Fetch('user', $order['user_id']);
    $team = Table::Fetch('team', $order['team_id']);
    $ticket = Table::Fetch('ticket', $order['team_id']);
    //find coupons
    $coupons = DB::LimitQuery('coupon', array(
               'condition' => array(
                    'order_id' => $order['id'],
                    'user_id'=> $user['id'],
                    ),  
        ));
    $order['referer'] = Table::Fetch('referer', $id, 'order_id');
    
    if ($team['delivery'] == 'express') {
        $option_express = option_category('express');
        $order['express_name'] = $option_express[$order['express_id']];
    }
    
    $coupon = Table::Fetch('coupon', $id);
    $paystate = array(
        'unpay' => '<font color="green">未付款</font>',
        'pay' => '<font color="red">已付款</font>',
    );
    $option_refund = array(
        'credit' => '退款到账户余额',
        'online' => '其他途径已退款',
    );
    
    $html = render('manage_ajax_dialog_torderview');
    json($html, 'dialog');
}
else if ( 'inviteok' == $action ) {
	need_auth('admin');
	$express_id = abs(intval($_GET['eid']));
	$invite = Table::Fetch('invite', $id);
	if (!$invite || $invite['pay']!='N') {
		json('非法操作', 'alert');
	}
	if(!$invite['team_id']) {
		json('没有发生购买行为，不能执行返利', 'alert');
	}
	$team = Table::Fetch('team', $invite['team_id']);
	$team_state = team_state($team);
	if (!in_array($team_state, array('success', 'soldout'))) {
		json('只有成功的团购才可以执行邀请返利', 'alert');
	}
	Table::UpdateCache('invite', $id, array(
				'pay' => 'Y', 
				'admin_id'=>$login_user_id,
				));
	$invite = Table::FetchForce('invite', $id);
	ZFlow::CreateFromInvite($invite);
	Session::Set('notice', '邀请返利操作成功');
	json(null, 'refresh');
}
else if ( 'inviteremove' == $action ) {
	need_auth('admin');
	Table::UpdateCache('invite', $id, array(
		'pay' => 'C',
		'admin_id' => $login_user_id,
	));
	Session::Set('notice', '不合法邀请记录取消成功！');
	json(null, 'refresh');
}
else if ( 'subscriberemove' == $action ) {
	need_auth('admin');
	$subscribe = Table::Fetch('subscribe', $id);
	if ($subscribe) {
        log_admin('misc', '删除邮件订阅:'.$subscribe['email'],$subscribe);
		ZSubscribe::Unsubscribe($subscribe);
		Session::Set('notice', "邮件地址：{$subscribe['email']}退订成功");
	}
	json(null, 'refresh');
}
else if ( 'smssubscriberemove' == $action ) {
	need_auth('admin');
	$subscribe = Table::Fetch('smssubscribe', $id);
	if ($subscribe) {
        log_admin('misc', '手机号码退订:'.$subscribe['mobile'],$subscribe);
		ZSMSSubscribe::Unsubscribe($subscribe['mobile']);
		Session::Set('notice', "手机号码：{$subscribe['mobile']}退订成功");
	}
	json(null, 'refresh');
}
else if ( 'partnerremove' == $action ) {
	need_auth('market');
	$partner = Table::Fetch('partner', $id);
	$count = Table::Count('team', array('partner_id' => $id) );
	if ($partner && $count==0) {
		Table::Delete('partner', $id);
		Session::Set('notice', "商户：{$id} 删除成功");
		json(null, 'refresh');
	}
	if ( $count > 0 ) {
		json('商户有团购项目，删除失败', 'alert'); 
	}
	json('商户删除失败', 'alert'); 
}
else if ( 'noticesms' == $action ) {
	need_auth('team');
	$nid = abs(intval($_GET['nid']));
	$now = time();
	$team = Table::Fetch('team', $id);
	$condition = array( 'team_id' => $id, );
	$coups = DB::LimitQuery('coupon', array(
				'condition' => $condition,
				'order' => 'ORDER BY id ASC',
				'offset' => $nid,
				'size' => 1,
				));
	if ( $coups ) {
		foreach($coups AS $one) {
			$nid++;
			sms_coupon($one);
		}
		json("X.misc.noticesms({$id},{$nid});", 'eval');
	} else {
		json($INI['system']['couponname'].'发送完毕', 'alert');
	}
}
else if ( 'noticesmssubscribe' == $action ) {
	need_auth('team');
	$nid = abs(intval($_GET['nid']));
	$team = Table::Fetch('team', $id);
	$condition = array( 'enable' => 'Y' );
	if(abs(intval($team['city_id']))) {
		$condition['city_id'] = abs(intval($team['city_id']));
	}
	$subs = DB::LimitQuery('smssubscribe', array(
				'condition' => $condition,
				'order' => 'ORDER BY id ASC',
				'offset' => $nid,
				'size' => 10,
				));
	$content = render('manage_tpl_smssubscribe');
	if ( $subs ) {
		$mobiles = Utility::GetColumn($subs, 'mobile');
		$nid += count($mobiles);
		$mobiles = implode(',', $mobiles);
		$smsr = sms_send($mobiles, $content);
		if ( true === $smsr ) {
			usleep(500000);
			json("X.misc.noticenextsms({$id},{$nid});", 'eval');
		} else {
			json("发送失败，错误码：{$smsr}", 'alert');
		}
	} else {
		json('订阅短信发送完毕', 'alert');
	}
}
else if ( 'noticesubscribe' == $action ) {
	need_auth('team');
	$nid = abs(intval($_GET['nid']));
	$now = time();
	$interval = abs(intval($INI['mail']['interval']));
	$team = Table::Fetch('team', $id);
	$partner = Table::Fetch('partner', $team['partner_id']);
	$city = Table::Fetch('city', $team['city_id']);

	$condition = array();
	if(abs(intval($team['city_id']))) {
		$condition['city_id'] = abs(intval($team['city_id']));
	}
	$subs = DB::LimitQuery('subscribe', array(
				'condition' => $condition,
				'order' => 'ORDER BY id ASC',
				'offset' => $nid,
				'size' => 1,
				));
	if ( $subs ) {
		foreach($subs AS $one) {
			$nid++;
			try{
				ob_start();
				mail_subscribe($city, $team, $partner, $one);
				ob_get_clean();
			}catch(Exception $e){}
			$cost = time() - $now;
			if ( $cost >= 20 ) {
				json("X.misc.noticenext({$id},{$nid});", 'eval');
			}
		}
		$cost = time() - $now;
		if ($interval && $cost < $interval) { sleep($interval - $cost); }
		json("X.misc.noticenext({$id},{$nid});", 'eval');
	} else {
		json('订阅邮件发送完毕', 'alert');
	}
}
elseif ( 'categoryedit' == $action ) {
	need_auth('admin');
	if ($id) {
		$category = Table::Fetch('category', $id);
		if (!$category) json('无数据', 'alert');
		$zone = $category['zone'];
	} else {
		$zone = strval($_GET['zone']);
	}
	if ( !$zone ) json('请确定分类', 'alert');
	$zone = get_zones($zone);

	/* 判断是否是快递公司,快递公司页面有价格表单 */
        if ($zone[0] == 'group') {
		  $newcategory = DB::LimitQuery('category', array(
						   'condition' => array( 
                                                    'zone' => 'group',
                                                    'fid' => '0',
                                                    'display' => 'Y') ,
						    ));
		  $newcategory = Utility::OptionArray($newcategory, 'id', 'name');
                  
                  $menu = DB::LimitQuery('menu',array('condition'=>array('display'=>1),));
                  $menu = Utility::OptionArray($menu, 'id', 'name');
                  
		  $html = render('manage/manage_ajax_dialog_categorygroup');
	}
        else if ($zone[0] == 'city') {
		  $newcategory = DB::LimitQuery('category', array(
						   'condition' => array( 
                                                    'zone' => 'province',
                                                    'fid' => '0',
                                                    'display' => 'Y') ,
						    ));
		  $newcategory = Utility::OptionArray($newcategory, 'id', 'name');
                  
		  $html = render('manage/manage_ajax_dialog_categorycity');
        }
        else{
		$html = render('manage/manage_ajax_dialog_categoryedit');
	}
	json($html, 'dialog');
}
elseif ( 'categoryremove' == $action ) {
	need_auth('admin');
	$category = Table::Fetch('category', $id);
	if (!$category) json('无此分类', 'alert');
	if ($category['zone'] == 'city') {
		$tcount = Table::Count('team', array('city_id' => $id));
		if ($tcount ) json('本类下存在团购项目', 'alert');
	}
	elseif ($category['zone'] == 'group') {
		$tcount = Table::Count('team', array('group_id' => $id));
		if ($tcount ) json('本类下存在团购项目', 'alert');
	}
	elseif ($category['zone'] == 'express') {
		$tcount = Table::Count('order', array('express_id' => $id));
		if ($tcount ) json('本类下存在订单项目', 'alert');
	}
	elseif ($category['zone'] == 'public') {
		$tcount = Table::Count('topic', array('public_id' => $id));
		if ($tcount ) json('本类下存在讨论区话题', 'alert');
	}
	Table::Delete('category', $id);
	option_category($category['zone']);
	Session::Set('notice', '删除分类成功');
	json(null, 'refresh');
}
else if ( 'teamcoupon' == $action ) {
	need_auth('team');
	$team = Table::Fetch('team', $id);
	team_state($team);
	if ($team['now_number']<$team['min_number']) {
		json('团购未结束或未达到最低成团人数', 'alert');
	}
	if ($team['delivery']!='coupon') json('本项目不发送优惠券', 'alert');

	/* all orders */
	$all_orders = DB::LimitQuery('order', array(
		'condition' => array(
			'team_id' => $id,		
			'state' => 'pay',
		),
	));
	$all_orders = Utility::AssColumn($all_orders, 'id');
	$all_order_ids = Utility::GetColumn($all_orders, 'id');
	$all_order_ids = array_unique($all_order_ids);

	/* all coupon id */
	$coupon_sql = "SELECT order_id, count(1) AS count FROM coupon WHERE team_id = '{$id}' GROUP BY order_id";
	$coupon_res = DB::GetQueryResult($coupon_sql, false);
	$coupon_order_ids = Utility::GetColumn($coupon_res, 'order_id');
	$coupon_order_ids = array_unique($coupon_order_ids);

	/* miss id */
	$miss_ids = array_diff($all_order_ids, $coupon_order_ids);
	foreach($coupon_res AS $one) {
		if ($one['count'] < $all_orders[$one['order_id']]['quantity']) {
			$miss_ids[] = $one['order_id'];
		}
	}
	$orders = Table::Fetch('order', $miss_ids);

	foreach($orders AS $order) {
		ZCoupon::Create($order);
	}
	json('发券成功',  'alert');
}
elseif ( $action == 'partnerhead' ) {
	$partner = Table::Fetch('partner', $id);
	$head = ($partner['head']==0) ? time() : 0;
	Table::UpdateCache('partner', $id, array( 'head' => $head,));
	$tip = $head ? '设置商户置顶成功' : '取消商户置顶成功';
	Session::Set('notice', $tip);
	json(null, 'refresh');
}
elseif ( 'cacheclear' == $action ) {
	need_auth('admin');
	$root = DIR_COMPILED;
	$handle = opendir($root);
	$templatelist = array( 'default'=> 'default',);
	$clear = $unclear = 0;
	while($one = readdir($handle)) {
		if ( strpos($one,'.') === 0 ) continue;
		$onefile = $root . '/' . $one;
		if ( is_dir($onefile) ) continue;
		if(@unlink($onefile)) { $clear ++; }
		else { $unclear ++; }
	}
    log_admin('system', '清空模板缓存'.$clear.'个');
	json("操作成功，清空缓存文件{$clear}个，未清空{$unclear}个", 'alert');
}
else if ( 'refundview' == $action) {
	$order = Table::Fetch('order', $id);
	$user = Table::Fetch('user', $order['user_id']);
	$team = Table::Fetch('team', $order['team_id']);
    
	//find coupons
	$coupons = DB::LimitQuery('coupon', array(
			'condition' => array(
		            'order_id' => $order['id'],
		            'user_id'=> $user['id'],
		            ),  
		));

	if ($team['delivery'] == 'express') {
		$option_express = option_category('express');
		$order['express_name'] = $option_express[$order['express_id']];
	}

	$payservice = array(
		'alipay' => '支付宝',
		'tenpay' => '财付通',
		'chinabank' => '网银在线',
		'credit' => '余额付款',
		'cash' => '线下支付',
        'yeepay' => '易宝',
		'sdopay' => '盛付通',
		'cmpay' => '手机支付',
		'paypal' => 'Paypal',
	);
	$paystate = array(
		'unpay' => '<font color="green">未付款</font>',
		'pay' => '<font color="red">已付款</font>',
	);

    if ($team['delivery'] == 'coupon') {
	$option_refund = array(
		'norefund' => '审核未通过',
		'credit' => '退款到账户余额',
		'online' => '其他途径已退款',
	);
	}else{
	$option_refund = array(
		'credit' => '退款到账户余额',
		'online' => '其他途径已退款',
	);
	}
	
	$html = render('manage_ajax_dialog_refundview');
	json($html, 'dialog');
}

else if ( 'refundcheck' == $action) {
	need_auth('admin');
	$rid = strtolower(strval($_GET['rid']));
	$cid = $_GET['cid'];
    if($cid=='' && $rid!='norefund'){
		json('您没有选择优惠券', 'alert');
		return false;
	 }
	$cids = explode( ',', $cid );
	$order = Table::Fetch('order', $id);
    if ( $order['card_id'] ) {
		Table::UpdateCache('order', $order['id'], array('rstate'=>'norefund'));
	    json(array(
			array('data' => '该订单为使用代金券订单,不支持分券退款！', 'type'=>'alert'),
			array('data' => 'X.boxClose();', 'type'=>'eval'),
			array('data' => 'null', 'type'=>'refresh'),
				  ),
			'mix');
	}
	if ( $rid == 'norefund' ) {	
		Table::UpdateCache('order', $order['id'], array('rstate'=>'norefund'));
		json(array(
			array('data' => '设置成功,该申请未通过！', 'type'=>'alert'),
			array('data' => 'X.boxClose();', 'type'=>'eval'),
			array('data' => 'null', 'type'=>'refresh'),
				  ),
			'mix');
	} else if($rid == 'credit'){
	    ZFlow::CouponRefund($order,$cids);
	} else {
		ZFlow::CouponOtherRefund($order,$cids);
	}

	/* team -- */
	$team = Table::Fetch('team', $order['team_id']);
	team_state($team);
	if ( $team['state'] != 'failure' ) {
		$minus = $team['conduser'] == 'Y' ? 1 : count($cids);
		Table::UpdateCache('team', $team['id'], array(
					'now_number' => array( "now_number - {$minus}", ),
		));
	}
	/* coupons delete*/
	foreach($cids AS $key => $value){
		Table::Delete('coupon', $value);
	}

	Session::Set('notice', '退款成功');
	json(null, 'refresh');
}
else if ( $action == 'orderremark' ) {
	$u = array(
		'adminremark' => strval($_GET['m']),
	);
	Table::UpdateCache('order', $id, $u);
	json(array(
			array('data' => "修改备注信息成功", 'type'=>'alert'),
			array('data' => null, 'type'=>'refresh'),
		), 'mix');
}
if ( 'newsremove' == $action) {
	need_auth('news');
	$news = Table::Fetch('news', $id);
	Table::Delete('news', $id);
	Session::Set('notice', "新闻 {$id} 删除成功！");
	json(null, 'refresh');
}
