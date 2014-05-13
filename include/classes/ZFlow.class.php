<?php
class ZFlow {
	static public function CreateFromOrder($order) {
		//update user money;
		$user = Table::Fetch('user', $order['user_id']);
		Table::UpdateCache('user', $order['user_id'], array(
					'money' => array( "money - {$order['origin']}" ),
					));

		$u = array(
				'user_id' => $order['user_id'],
				'money' => $order['origin'],
				'direction' => 'expense',
				'action' => 'buy',
				'detail_id' => $order['team_id'],
				'create_time' => time(),
				);
		$q = DB::Insert('flow', $u);
	}

	static public function CreateFromCoupon($coupon) {
		if ( $coupon['credit'] <= 0 ) return 0;

		//update user money;
		$user = Table::Fetch('user', $coupon['user_id']);
		Table::UpdateCache('user', $coupon['user_id'], array(
					'money' => array( "money + {$coupon['credit']}" ),
					));

		$u = array(
				'user_id' => $coupon['user_id'],
				'money' => $coupon['credit'],
				'direction' => 'income',
				'action' => 'coupon',
				'detail_id' => $coupon['id'],
				'create_time' => time(),
				);
		return DB::Insert('flow', $u);
	}

	static public function CreateFromRefund($order) {
		global $login_user_id;
		if ( $order['state']!='pay' || $order['origin']<=0 ) return 0;

		//update user money;
		$user = Table::Fetch('user', $order['user_id']);
		Table::UpdateCache('user', $order['user_id'], array(
					'money' => array( "money + {$order['origin']}" ),
					));
		//update order
		Table::UpdateCache('order', $order['id'], array('state'=>'unpay','rstate' => 'berefund'));

		$u = array(
				'user_id' => $order['user_id'],
				'admin_id' => $login_user_id,
				'money' => $order['origin'],
				'direction' => 'income',
				'action' => 'refund',
				'detail_id' => $order['team_id'],
				'create_time' => time(),
				);
		return DB::Insert('flow', $u);
	}

	static public function CouponRefund($order,$coupons) {
		global $login_user_id;
		$count = count($coupons);
		if ( $order['state']!='pay' || $order['origin']<=0 || $count<=0 ) return 0; 
		$state = ($order['quantity']==$count) ? 'berefund' : 'normal';
		if($state=='berefund'){
		//update user money;
		$user = Table::Fetch('user', $order['user_id']);
		Table::UpdateCache('user', $order['user_id'], array(
					'money' => array( "money + {$order['origin']}" ),
					));
        //update credit
		ZCredit::Refund($order['user_id'], $order);
		//update order
		Table::UpdateCache('order', $order['id'], array('state'=>'unpay','rstate'=>$state));
		$u = array(
				'user_id' => $order['user_id'],
				'admin_id' => $login_user_id,
				'money' => $order['origin'],
				'direction' => 'income',
				'action' => 'refund',
				'detail_id' => $order['team_id'],
				'create_time' => time(),
				);
		return DB::Insert('flow', $u);
        }else{		
        $money = $order['price'] * $count; 
		//update user money;			  
		$user = Table::Fetch('user', $order['user_id']);
		Table::UpdateCache('user', $order['user_id'], array(
					'money' => array( "money + {$money}" ),
					));	
		//update order
		Table::UpdateCache('order', $order['id'],     array(
			        'quantity' =>array( "quantity - {$count}" ),
			        'rstate' => $state,
			        'origin' =>array("origin -{$money}"),
			        ));
		$u = array(
				'user_id' => $order['user_id'],
				'admin_id' => $login_user_id,
				'money' => $money,
				'direction' => 'income',
				'action' => 'refund',
				'detail_id' => $order['team_id'],
				'create_time' => time(),
				);
		return DB::Insert('flow', $u);	
		}
	}

	static public function CouponOtherRefund($order,$coupons) {
		global $login_user_id;
		$count = count($coupons);
		if ( $order['state']!='pay' || $order['origin']<=0 || $count<=0 ) return 0; 
		$state = ($order['quantity']==$count) ? 'berefund' : 'normal';
		if($state=='berefund'){	    
        //update credit
		ZCredit::Refund($order['user_id'], $order);
		//update order
		return Table::UpdateCache('order', $order['id'], array('state'=>'unpay','rstate'=>$state,'service' => 'cash'));
        }else{		
        $money = $order['price'] * $count; 
		//update order
		return Table::UpdateCache('order', $order['id'],     array(
			        'quantity' =>array( "quantity - {$count}" ),
			        'rstate' => $state,
			        'origin' =>array("origin -{$money}"),
			        ));	
		}
	}

	static public function CreateFromInvite($invite) {
		global $login_user_id;
		if ( $invite['pay']!='Y' && $INI['system']['invitecredit']<=0 ) return 0;

		//update user money;
		$user = Table::Fetch('user', $invite['user_id']);
		Table::UpdateCache('user', $invite['user_id'], array(
					'money' => array( "money + {$invite['credit']}" ),
					));

		$u = array(
				'user_id' => $invite['user_id'],
				'admin_id' => $login_user_id,
				'money' => $invite['credit'],
				'direction' => 'income',
				'action' => 'invite',
				'detail_id' => $invite['other_user_id'],
				'create_time' => $invite['buy_time'],
				);
		return DB::Insert('flow', $u);
	}

	static public function CreateFromStore($user_id=0, $money=0) {
		global $login_user_id;
		$money = floatval($money);
		if ( $money == 0 || $user_id <= 0)  return;

		//update user money;
		$user = Table::Fetch('user', $user_id);
		Table::UpdateCache('user', $user_id, array(
					'money' => array( "money + {$money}" ),
					));

		/* switch store|withdraw */
		$direction = ($money>0) ? 'income' : 'expense';
		$action = ($money>0) ? 'store' : 'withdraw';
		$money = abs($money);
		/* end swtich */

		$u = array(
				'user_id' => $user_id,
				'admin_id' => $login_user_id,
				'money' => $money,
				'direction' => $direction,
				'action' => $action,
				'detail_id' => 0,
				'create_time' => time(),
				);
		return DB::Insert('flow', $u);
	}
    static public function CreateFromDaysign($user_id=0, $money=0) {
		global $login_user_id;
		$money = floatval($money);
		if ( $money == 0 || $user_id <= 0)  return;

		//update user money;
		$user = Table::Fetch('user', $user_id);
		Table::UpdateCache('user', $user_id, array(
					'money' => array( "money + {$money}" ),
					));
		$direction = 'income';
		$action = 'daysign';
		$u = array(
				'user_id' => $user_id,
				'admin_id' => $login_user_id,
				'money' => $money,
				'direction' => $direction,
				'action' => $action,
				'detail_id' => 0,
				'create_time' => time(),
				);
		return DB::Insert('flow', $u);
	}
	static public function CreateFromCharge($money,$user_id,$time,$service='alipay',$trade_no=''){
		global $option_service;
		if (!$money || !$user_id || !$time) return 0;
		$pay_id = "charge-{$user_id}-{$time}";
		$pay = Table::Fetch('pay', $pay_id);
		if ( $pay ) return 0;
		$order_id = ZOrder::CreateFromCharge($money,$user_id,$time,$service);
		if (!$order_id) return 0;

		//insert pay record
		$pay = array(
			'id' => $pay_id,
			'vid' => $trade_no,
			'order_id' => $order_id,
			'bank' => $option_service[$service],
			'currency' => 'CNY',
			'money' => $money,
			'service' => $service,
			'create_time' => $time,
		);
		DB::Insert('pay', $pay);
		ZCredit::Charge($user_id, $money);
		//end//

		//update user money;
		$user = Table::Fetch('user', $user_id);
		Table::UpdateCache('user', $user_id, array(
					'money' => array( "money + {$money}" ),
					));

		$u = array(
				'user_id' => $user_id,
				'admin_id' => 0,
				'money' => $money,
				'direction' => 'income',
				'action' => 'charge',
				'detail_id' => $pay_id,
				'create_time' => $time,
				);
		return DB::Insert('flow', $u);
	}

	static public function Explain($record=array()) {
		if (!$record) return null;
		$action = $record['action'];
		if ('buy' == $action) {
			$team = Table::Fetch('team', $record['detail_id']);
			if ($team) {
				return  "购买项目 - <a href=\"/team.php?id={$team['id']}\">{$team['title']}</a>";
			} else {
				return "购买项目 - 该项目已删除";
			}
		}
		else if ( 'invite' == $action) {
			$user = Table::Fetch('user', $record['user_id']);
			return "邀请返利 - 好友{$user['username']}注册并购买";
		}
		else if ( 'store' == $action) {
			return '现金充值';
		}
		else if ( 'withdraw' == $action) {
			return '现金提现';
		}
		else if ( 'coupon' == $action) {
			return "消费返利 - 优惠券消费返利";
		}
		else if ( 'refund' == $action) {
			$team = Table::Fetch('team', $record['detail_id']);
			if ($team) {
				return  "项目退款 - <a href=\"/team.php?id={$team['id']}\">{$team['title']}</a>";
			} else {
				return "项目退款 - 该项目已删除";
			}
		}
		else if ( 'charge' == $action) {
			return "在线充值";
		}
                else if ( 'cardstore' == $action) {
			return '充值卡充值';
		}
                else if ( 'paycharge' == $action) {
			return '购买充值';
		}
		else if ( 'register' == $action) {
			return "注册送钱";
		}
        else if ( 'daysign' == $action) {
			return "每日签到";
		}
	}
}
?>
