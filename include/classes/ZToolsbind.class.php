<?php
class ZToolsbind
{
	static public function Create($mobile, $user_id, $secret=null, $enable=false) 
	{
		if (!Utility::IsMobile($mobile, true)) return;
		$secret = $secret ? $secret : Utility::VerifyCode();
		$table = new Table('toolsbind', array(
                                        'user_id' => $user_id,
					'tools' => $mobile,
					'enable' => $enable ? 'Y' : 'N',
					'secret' => $secret,
					));
		$condition = array( 
		      'user_id' => $user_id,
		      'tools' => $mobile, 
		      'enable' => 'N',
		);
		$haveone = DB::GetTableRow('toolsbind', $condition);
                if ($haveone){
		 	return Table::UpdateCache('toolsbind', $haveone['id'], array(
		        'secret' => $secret,
                        'enable' => 'N',
			         ));
		}
                 //已经绑定了本号码
                $loginbind = array( 
	              'user_id' => $user_id,
		      'tools' => $mobile, 
		      'enable' => 'Y',
		);
		$havebind = DB::GetTableRow('toolsbind', $loginbind);
                if ($havebind){
		 	return false;
		}
                
		//$table->insert(array( 'user_id', 'tools','secret', 'enable'));
		DB::Insert('toolsbind', array(
		'user_id' => $user_id,
		'tools' => $mobile,
		'secret' => $secret,
		'enable' => 'N',
		'create_time' => time(),
	    ));
		$have = Table::Fetch('toolsbind', $mobile, 'tools');
		if ( $have && 'Y'==($have['enable'])
		   ) {
			return true;
		}
	}

	static public function Enable($mobile, $enable=false,$user_id='') {
        $condition = array( 
		      'tools' => $mobile, 
		      'enable' => 'Y',
		);
		$remove = DB::GetTableRow('toolsbind', $condition);
                if ($remove){
			Table::Delete('toolsbind', $remove['id']);
		}
        $havecondition = array( 
		      'user_id' => $user_id, 
		      'enable' => 'Y',
		);
		$removeold = DB::GetTableRow('toolsbind', $havecondition);
                if ($removeold){
			Table::Delete('toolsbind', $removeold['id']);
		}

		$sms = Table::Fetch('toolsbind', $mobile, 'tools');
                $time = time();
		if ( $sms ) {
			Table::UpdateCache('toolsbind', $sms['id'], array(
				'enable' => 'Y',
                'create_time' => $time,
			));
                Table::UpdateCache('user', $sms['user_id'], array(
				'mobile' => $mobile,
				'mobilecode' => 'yes',
                'enable' => 'Y',
			));
		} 
	}


}
