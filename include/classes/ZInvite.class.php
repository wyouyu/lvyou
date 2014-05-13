<?php
class ZInvite
{
	static public function CreateNewId($other_user_id) {
		$_rid = abs(intval(cookieget('_rid')));
		$other_user_id = abs(intval($other_user_id));
		if ($_rid==0 || $other_user_id==0) return;
		self::CreateFromId($_rid, $other_user_id);
	}
	
	static public function Create($ruser, $newuser) {
		if ($ruser['id'] == $newuser['id']) return;
		if (!$ruser['id'] || !$newuser['id']) return;
		cookieset('_rid', null, -1);
		if ($newuser['newbie'] == 'N') return;
		$have = Table::Fetch('invite', $newuser['id'], 'other_user_id');
		cookieset('_rid', null, -1);
		if ( $have ) return false;
		$invite = array(
			'user_id' => $ruser['id'],
			'user_ip' => $ruser['ip'],
			'other_user_id' => $newuser['id'],
			'other_user_ip' => $newuser['ip'],
			'create_time' => time(),
		);
		return DB::Insert('invite', $invite);
	}

	static public function CreateFromId($user_id, $other_user_id) {
		if (!$user_id || !$other_user_id) return;
		if ($user_id == $other_user_id) return;
		$ruser = Table::Fetch('user', $user_id);
		$newuser = Table::Fetch('user', $other_user_id);
		if ( $newuser['newbie'] == 'Y' ) {
			cookieset('_rid', null, -1);
			self::Create($ruser, $newuser);
		}
	}

	static public function CreateFromBuy($other_user_id) {
		$rid = abs(intval(cookieget('_rid')));
		return self::CreateFromId($rid, $other_user_id);
	}

	static public function CheckInvite($order) {
		if ( $order['state'] == 'unpay' ) return;
		$user = Table::Fetch('user', $order['user_id']);
		$team = Table::Fetch('team', $order['team_id']);
		if ( !$user || $user['newbie'] != 'Y' ) return;
		Table::UpdateCache('user', $order['user_id'], array(
			'newbie' => 'N',
		));

		global $INI;
		$invite = Table::Fetch('invite',$order['user_id'],'other_user_id');
		$invitecredit = abs(intval($team['bonus']));

		/* 无邀请记录 或 已返利或取消 */
		if (!$invite || $invite['credit']>0 || $invite['pay']!='N') {
			return;
		}
		if (time() - $invite['create_time'] > 7*86400) {
			return;
		}
		Table::UpdateCache('invite', $invite['id'], array(
			'credit' => $invitecredit,
			'team_id' => $order['team_id'],
			'buy_time' => time(),
		));
		return true;
	}
}
