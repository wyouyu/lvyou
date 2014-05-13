<?php
/* import other */
import('configure');
import('current');
import('rewrite');
import('utility');
import('mailer');
import('pay');
import('sms');
import('upgrade');
import('uc');
import('cron');
import('logger');

function template($tFile) {
	global $INI;
	if ( 0===strpos($tFile, 'manage') ) {
		return __template($tFile);
	}
	if ($INI['skin']['template']) {
		$templatedir = DIR_TEMPLATE. '/' . $INI['skin']['template'];
		$checkfile = $templatedir . '/html_header.html';
		if ( file_exists($checkfile) ) {
			return __template($INI['skin']['template'].'/'.$tFile);
		}
	}
	return __template($tFile);
}

function render($tFile, $vs=array()) {
    ob_start();
    foreach($GLOBALS AS $_k=>$_v) {
        ${$_k} = $_v;
    }
	foreach($vs AS $_k=>$_v) {
		${$_k} = $_v;
	}
	include template($tFile);
    return render_hook(ob_get_clean());
}

function render_hook($c) {
	global $INI;
	$c = preg_replace('#href="/#i', 'href="'.WEB_ROOT.'/', $c);
	$c = preg_replace('#src="/#i', 'src="'.WEB_ROOT.'/', $c);
	$c = preg_replace('#action="/#i', 'action="'.WEB_ROOT.'/', $c);

	/* theme */
	$page = strval($_SERVER['REQUEST_URI']);
	if($INI['skin']['theme'] && !preg_match('#/manage/#i',$page)) {
		$themedir = WWW_ROOT. '/static/theme/' . $INI['skin']['theme'];
		$checkfile = $themedir. '/css/index.css';
		if ( file_exists($checkfile) ) {
			$c = preg_replace('#/static/css/#', "/static/theme/{$INI['skin']['theme']}/css/", $c);
			$c = preg_replace('#/static/img/#', "/static/theme/{$INI['skin']['theme']}/img/", $c);
		}
	}
	$c = preg_replace('#([\'\=\"]+)/static/#', "$1{$INI['system']['cssprefix']}/static/", $c);
	if (strtolower(cookieget('locale','zh_cn'))=='zh_tw') {
		require_once(DIR_FUNCTION  . '/tradition.php');
		$c = str_replace(explode('|',$_charset_simple), explode('|',$_charset_tradition),$c);
	}
	/* encode id */
	$c = rewrite_hook($c);
	$c = obscure_rep($c);
	return $c;
}

function output_hook($c) {
	global $INI;
	if ( 0==abs(intval($INI['system']['gzip'])))  die($c);
	$HTTP_ACCEPT_ENCODING = $_SERVER["HTTP_ACCEPT_ENCODING"]; 
	if( strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false ) 
		$encoding = 'x-gzip'; 
	else if( strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false ) 
		$encoding = 'gzip'; 
	else $encoding == false;
	if (function_exists('gzencode')&&$encoding) {
		$c = gzencode($c);
		header("Content-Encoding: {$encoding}"); 
	}
	$length = strlen($c);
	header("Content-Length: {$length}");
	die($c);
}

$lang_properties = array();
function I($key) { 
    global $lang_properties, $LC;
    if (!$lang_properties) {
        $ini = DIR_ROOT . '/i18n/' . $LC. '/properties.ini';
        $lang_properties = Config::Instance($ini);
    }
    return isset($lang_properties[$key]) ?
        $lang_properties[$key] : $key;
}

function json($data, $type='eval') {
    $type = strtolower($type);
    $allow = array('eval','alert','updater','dialog','mix', 'refresh');
    if (false==in_array($type, $allow))
        return false;
    Output::Json(array( 'data' => $data, 'type' => $type,));
}

function redirect($url=null, $notice=null, $error=null) {
	$url = $url ? obscure_rep($url) : $_SERVER['HTTP_REFERER'];
	$url = $url ? $url : '/';
	if ($notice) Session::Set('notice', $notice);
	if ($error) Session::Set('error', $error);
    header("Location: {$url}");
    exit;
}
function write_php_file($array, $filename=null){
	$v = "<?php\r\n\$INI = ";
	$v .= var_export($array, true);
	$v .=";\r\n?>";
	return file_put_contents($filename, $v);
}

function write_ini_file($array, $filename=null){   
	$ok = null;   
	if ($filename) {
		$s =  ";;;;;;;;;;;;;;;;;;\r\n";
		$s .= ";; SYS_INIFILE\r\n";
		$s .= ";;;;;;;;;;;;;;;;;;\r\n";
	}
	foreach($array as $k=>$v) {   
		if(is_array($v))   { 
			if($k != $ok) {   
				$s  .=  "\r\n[{$k}]\r\n";
				$ok = $k;   
			} 
			$s .= write_ini_file($v);
		}else   {   
			if(trim($v) != $v || strstr($v,"["))
				$v = "\"{$v}\"";   
			$s .=  "$k = \"{$v}\"\r\n";
		} 
	}

	if(!$filename) return $s;   
	return file_put_contents($filename, $s);
}   

function save_config($type='ini') {
	return configure_save();
	global $INI; $q = ZSystem::GetSaveINI($INI);
	if ( strtoupper($type) == 'INI' ) {
		if (!is_writeable(SYS_INIFILE)) return false;
		return write_ini_file($q, SYS_INIFILE);
	} 
	if ( strtoupper($type) == 'PHP' ) {
		if (!is_writeable(SYS_PHPFILE)) return false;
		return write_php_file($q, SYS_PHPFILE);
	} 
	return false;
}

function save_system($ini) {
	$system = Table::Fetch('system', 1);
	$ini = ZSystem::GetUnsetINI($ini);
	$value = Utility::ExtraEncode($ini);
	$table = new Table('system', array('value'=>$value));
	if ( $system ) $table->SetPK('id', 1);
	return $table->update(array( 'value'));
}

/* user relative */
function need_login($wap=false) {
	if ( isset($_SESSION['user_id']) ) {
		if (is_post()) {
			unset($_SESSION['loginpage']);
			unset($_SESSION['loginpagepost']);
		}
		return $_SESSION['user_id'];
	}
	if ( is_get() ) {
		Session::Set('loginpage', $_SERVER['REQUEST_URI']);
	} else {
		Session::Set('loginpage', $_SERVER['HTTP_REFERER']);
		Session::Set('loginpagepost', json_encode($_POST));
	}
	if (true===$wap) {
		return redirect('login.php');	
	}
	return redirect(WEB_ROOT . '/account/login.php');	
}
function need_post() {
	return is_post() ? true : redirect(WEB_ROOT . '/index.php');
}
function need_manager($super=false) {
	if ( ! is_manager() ) {
		redirect( WEB_ROOT . '/manage/login.php' );
	}
	if ( ! $super ) return true;
	if ( abs(intval($_SESSION['user_id'])) == 1 ) return true;
	return redirect( WEB_ROOT . '/manage/misc/index.php');
}
function need_partner() {
	return is_partner() ? true : redirect( WEB_ROOT . '/biz/login.php');
}

function need_open($b=true) {
	if (true===$b) {
		return true;
	}
	if ($AJAX) json('本功能未开放', 'alert');
	Session::Set('error', '你访问的功能页未开放');
	redirect( WEB_ROOT . '/index.php');
}

function need_auth($b=true) {
	global $AJAX, $INI, $login_user;
	if (is_string($b)) {
		$auths = $INI['authorization'][$login_user['id']];
		$bs = explode('|', $b);
		$b = is_manager(true); 
		if ($b) return true;
		foreach($bs AS $bo) if(!$b) $b = in_array($bo, $auths);
	}
	if (true===$b) {
		return true;
	}
	if ($AJAX) json('无权操作', 'alert');
	die(include template('manage_misc_noright'));
}

function is_manager($super=false, $weak=false) {
	global $login_user;
	if ( $weak===false && 
			( !$_SESSION['admin_id'] 
			  || $_SESSION['admin_id'] != $login_user['id']) ) {
		return false;
	}
	if ( ! $super ) return ($login_user['manager'] == 'Y');
	return $login_user['id'] == 1;
}
function is_partner() {
	return ($_SESSION['partner_id']>0);
}

function is_newbie(){ return (cookieget('newbie')!='N'); }
function is_get() { return ! is_post(); }
function is_post() {
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
}

function is_login() {
	return isset($_SESSION['user_id']);
}

function get_loginpage($default=null) {
	$loginpage = Session::Get('loginpage', true);
	if ($loginpage)  return $loginpage;
	if ($default) return $default;
	return WEB_ROOT . '/index.php';
}

function cookie_city($city) {
	global $hotcities;
	if($city) { 
		cookieset('city', $city['id']);
		return $city;
	} 
	$city_id = cookieget('city'); 
	$city = Table::Fetch('category', $city_id);
	if (!$city && option_yes('citylocal')) $city = get_city();
	if (!$city) $city = array_shift($hotcities);
	if ($city) return cookie_city($city);
	return $city;
}

function getItemsByCondition($condition)
{
    $today = strtotime(date('Y-m-d'));
    $condition['team_type'] =  'normal';
    $condition[] ="begin_time <= {$today}";
    $condition[] ="end_time > {$today}";
    //print_r($condition);
    $order = 'order by sort_order DESC,id DESC';
    $teams = DB::LimitQuery('team', array(
                'condition' => $condition,
                'size' => 6,
                'order' => $order,
                ));
    return $teams;
}

function getPartnerByCondition()
{
    $condition['type'] =  '4';
    $condition['shouye'] ="1";
    $order = 'order by id DESC ';
    $partners = DB::LimitQuery('partner', array(
                'condition' => $condition,
                'size' => 5,
                'order' => $order,
                ));
    return $partners;
}

function getgentuantehui()
{
   $query ="select a.*,b.price from `partner` as a inner join gentuanyou as b on a.id=b.partner_id where a.type=3 and b.tehui=1  order by head desc limit 0,3";
	$re = DB::GetQueryResult($query,false);
    return $re;
}

function getxihuan()
{
	$query ="select a.*,b.price from `partner` as a inner join gentuanyou as b on a.id=b.partner_id where a.type=3 and a.hot=1  order by head desc limit 0,3";
	$re = DB::GetQueryResult($query,false);
    return $re;
}

function getzhoubian()
{
	$query ="select a.*,b.price from `partner` as a inner join gentuanyou as b on a.id=b.partner_id where a.type=3 and b.leixing=3  order by head desc limit 0,6";
	$re = DB::GetQueryResult($query,false);
    return $re;
}

function getguonei()
{
	$query ="select a.*,b.price from `partner` as a inner join gentuanyou as b on a.id=b.partner_id where a.type=3 and b.leixing=1  order by head desc limit 0,7";
	$re = DB::GetQueryResult($query,false);
    return $re;
}

function getchujing()
{
	$query ="select a.*,b.price from `partner` as a inner join gentuanyou as b on a.id=b.partner_id where a.type=3 and b.leixing=2  order by head desc limit 0,6";
	$re = DB::GetQueryResult($query,false);
    return $re;
}

function getTodayList()
{
    $today = strtotime(date('Y-m-d'));
    $condition['team_type'] =  'normal';
    $condition[] ="begin_time <= {$today}";
    $condition[] ="end_time > {$today}";
    $order = 'order by id DESC';
    $teams = DB::LimitQuery('team', array(
                'condition' => $condition,
                'size' => 6,
                'order' => $order,
                ));
    return $teams;
}
function ename_city($ename=null) {
	return DB::LimitQuery('category', array(
		'condition' => array(
			'zone' => 'city',
			'ename' => $ename,
		),
		'one' => true,
	));
}

function cookieset($k, $v, $expire=0) {
	$pre = substr(md5($_SERVER['HTTP_HOST']),0,4);
	$k = "{$pre}_{$k}";
	if ($expire==0) {
		$expire = time() + 365 * 86400;
	} else {
		$expire += time();
	}
	setCookie($k, $v, $expire, '/');
}

function cookieget($k, $default='') {
	$pre = substr(md5($_SERVER['HTTP_HOST']),0,4);
	$k = "{$pre}_{$k}";
	return isset($_COOKIE[$k]) ? strval($_COOKIE[$k]) : $default;
}

function moneyit($k) {
	return rtrim(rtrim(sprintf('%.2f',$k), '0'), '.');
}

function debug($v, $e=false) {
	global $login_user_id;
	if ($login_user_id==100000) {
		echo "<pre>";
		var_dump( $v);
		if($e) exit;
	}
}

function getparam($index=0, $default=0) {
	if (is_numeric($default)) {
		$v = abs(intval($_GET['param'][$index]));
	} else $v = strval($_GET['param'][$index]);
	return $v ? $v : $default;
}
function getpage() {
	$c = abs(intval($_GET['page']));
	return $c ? $c : 1;
}
function pagestring($count, $pagesize, $wap=false) {
	$p = new Pager($count, $pagesize, 'page');
	if ($wap) {
		return array($pagesize, $p->offset, $p->genWap());
	}
	return array($pagesize, $p->offset, $p->genBasic());
}

function pagestring_search($count, $pagesize, $wap=false)
{
    $p = new Pager($count, $pagesize, 'page');
    if ($wap) {
        return array($pagesize, $p->offset, $p->genWap());
    }
    return array($pagesize, $p->offset, $p->genBasic_search());
}

function pagestring_group($count, $pagesize, $wap=false)
{
    $p = new Pager($count, $pagesize, 'page');
    if ($wap) {
        return array($pagesize, $p->offset, $p->genWap());
    }
    return array($pagesize, $p->offset, $p->genBasic_group());
}


function uencode($u) {
	return base64_encode(urlEncode($u));
}
function udecode($u) {
	return urlDecode(base64_decode($u));
}

/* share link */
function share_renren($team) {
	global $login_user_id;
	global $INI;
	$user = Table::Fetch('user',$login_user_id);
	$mail = uencode($user['email']);
	if ($team)  {
		$query = array(
				'link' => $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$mail}",
				'title' => $team['title'],
				);
	}
	else {
		$query = array(
				'link' => $INI['system']['wwwprefix'] . "/r.php?r={$mail}",
				'title' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				);
	}

	$query = http_build_query($query);
	return 'http://share.renren.com/share/buttonshare.do?'.$query;
}

function share_kaixin($team) {
	global $login_user_id;
	global $INI;
	$user = Table::Fetch('user',$login_user_id);
	$mail = uencode($user['email']);
	if ($team)  {
		$query = array(
				'rurl' => $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$mail}",
				'rtitle' => $team['title'],
				'rcontent' => strip_tags($team['summary']),
				);
	}
	else {
		$query = array(
				'rurl' => $INI['system']['wwwprefix'] . "/r.php?r={$mail}",
				'rtitle' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				'rcontent' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				);
	}
	$query = http_build_query($query);
	return 'http://www.kaixin001.com/repaste/share.php?'.$query;
}

function share_douban($team) {
	global $login_user_id;
	global $INI;
	$user = Table::Fetch('user',$login_user_id);
	$mail = uencode($user['email']);
	if ($team)  {
		$query = array(
				'url' => $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$mail}",
				'title' => $team['title'],
				);
	}
	else {
		$query = array(
				'url' => $INI['system']['wwwprefix'] . "/r.php?r={$mail}",
				'title' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				);
	}
	$query = http_build_query($query);
	return 'http://www.douban.com/recommend/?'.$query;
}

function share_sina($team) {
	global $login_user_id;
	global $INI;
	$user = Table::Fetch('user',$login_user_id);
	$mail = uencode($user['email']);
	if ($team)  {
		$query = array(
				'url' => $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$mail}",
				'title' => $team['title'],
				);
	}
	else {
		$query = array(
				'url' => $INI['system']['wwwprefix'] . "/r.php?r={$mail}",
				'title' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				);
	}
	$query = http_build_query($query);
	return 'http://v.t.sina.com.cn/share/share.php?'.$query;
}

function share_tencent($team) {
	global $login_user_id;
	global $INI;
	$user = Table::Fetch('user',$login_user_id);
	$mail = uencode($user['email']);
	if ($team)  {
		$query = array(
				'url' => $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$mail}",
				'title' => $team['title'],
				);
	}
	else {
		$query = array(
				'url' => $INI['system']['wwwprefix'] . "/r.php?r={$mail}",
				'title' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				);
	}
	$query = http_build_query($query);
	return 'http://v.t.qq.com/share/share.php?'.$query;
}
function share_mail($team) {
	global $login_user_id;
	global $INI;
	$user = Table::Fetch('user',$login_user_id);
	$mail = uencode($user['email']);
	if (!$team) {
		$team = array(
				'title' => $INI['system']['sitename'] . '(' . $INI['system']['wwwprefix'] . ')',
				);
	}
	$pre[] = "发现一好网站--{$INI['system']['sitename']}，他们每天组织一次团购，超值！";
	if ( $team['id'] ) {
		$pre[] = "今天的团购是：{$team['title']}";
		$pre[] = "我想你会感兴趣的：";
		$pre[] = $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$mail}";
		$pre = mb_convert_encoding(join("\n\n", $pre), 'GBK', 'UTF-8');
		$sub = "有兴趣吗：{$team['title']}";
	} else {
		$sub = $pre[] = $team['title'];
	}
	$sub = mb_convert_encoding($sub, 'GBK', 'UTF-8');
	$query = array( 'subject' => $sub, 'body' => $pre, );
	$query = http_build_query($query);
	return 'mailto:?'.$query;
}

function domainit($url) {
	if(strpos($url,'//')) { preg_match('#[//]([^/]+)#', $url, $m);
} else { preg_match('#[//]?([^/]+)#', $url, $m); }
return $m[1];
}

// that the recursive feature on mkdir() is broken with PHP 5.0.4 for
function RecursiveMkdir($path) {
	if (!file_exists($path)) {
		RecursiveMkdir(dirname($path));
		@mkdir($path, 0777);
	}
}
//图片上传函数
function upload_image($input, $image=null, $type='team', $scale=false) {
	$year = date('Y'); $day = date('md'); $n = time().rand(1000,9999).'.jpg';
	$z = $_FILES[$input];
	if ($z && strpos($z['type'], 'image')===0 && $z['error']==0) {
		//if (!$image) { 
			RecursiveMkdir( IMG_ROOT . '/' . "{$type}/{$year}/{$day}" );
			$image = "{$type}/{$year}/{$day}/{$n}";
			$path = IMG_ROOT . '/' . $image;
		//} else {
			//RecursiveMkdir( dirname(IMG_ROOT .'/' .$image) );
			//$path = IMG_ROOT . '/' .$image;
		//}
		if ($type=='user') {
			Image::Convert($z['tmp_name'], $path, 48, 48, Image::MODE_CUT);
		} 
		else if($type=='team') {
			move_uploaded_file($z['tmp_name'], $path);
		}
		else if($type=='slide') {
			move_uploaded_file($z['tmp_name'], $path);
		}
		if($type=='team' && $scale) {
			$npath = preg_replace('#(\d+)\.(\w+)$#', "\\1_index.\\2", $path); 
			Image::Convert($path, $npath, 200, 120, Image::MODE_CUT);
		}
		return $image;
	} 
	return $image;
}
//批量上传图片
function upload_images($input, $image=null, $type='team', $scale=false) {
    
    
    foreach ($_FILES[$input]["error"] as $key => $error) {  
        $year = date('Y'); $day = date('md'); $n = time().rand(1000,9999).'.jpg';
        if ($error == UPLOAD_ERR_OK) {
             //if(!$image)
             //{
                 RecursiveMkdir( IMG_ROOT . '/' . "{$type}/{$year}/{$day}" );
                 $image = "{$type}/{$year}/{$day}/{$n}";
                 $path = IMG_ROOT . '/' . $image;
             //}
            // else {
             //   RecursiveMkdir( dirname(IMG_ROOT .'/' .$image) );
              //  $path = IMG_ROOT . '/' .$image;
            // }
             if($type=='team')
             {
                 move_uploaded_file($_FILES[$input]["tmp_name"][$key], $path);
             }
             $image_str .= $image.'|';
         }
     } 
     return $image_str;
}

//批量修改图片
function edit_upload_images($input, $image=null, $type='team', $scale=false,$imgarr)
{
    //exit(print_r($imgarr));
    foreach ($_FILES[$input]["tmp_name"] as $key => $tmp) {
        if(!empty($tmp))
        {
            $year = date('Y'); $day = date('md'); $n = time().rand(1000,9999).'.jpg';
            RecursiveMkdir( IMG_ROOT . '/' . "{$type}/{$year}/{$day}" );
            $image = "{$type}/{$year}/{$day}/{$n}";
            $path = IMG_ROOT . '/' . $image;
            move_uploaded_file($_FILES[$input]["tmp_name"][$key], $path);
        }
        else
        {
            $image = $imgarr[$key];
        }
        $image_str .= $image.'|';
    }
    
    return $image_str;
}

function user_image($image=null) {
	global $INI;
	$image = $image ? $image : 'img/user-no-avatar.gif';
	return "/static/{$image}";
}

function team_image($image=null, $index=false) {
	global $INI;
	if (!$image) return null;
	if ($index) {
		$path = WWW_ROOT . '/static/' . $image;
		$image = preg_replace('#(\d+)\.(\w+)$#', "\\1_index.\\2", $image); 
		$dest = WWW_ROOT . '/static/' . $image;
		if (!file_exists($dest) && file_exists($path) ) {
			Image::Convert($path, $dest, 200, 120, Image::MODE_SCALE);
		}
	}
	return "{$INI['system']['imgprefix']}/static/{$image}";
}

function userreview($content) {
	$line = preg_split("/[\n\r]+/", $content, -1, PREG_SPLIT_NO_EMPTY);
	$r = '<ul>';
	foreach($line AS $one) {
		$c = explode('|', htmlspecialchars($one));
		$c[2] = $c[2] ? $c[2] : '/';
		$r .= "<li>{$c[0]}<span>－－<a href=\"{$c[2]}\" target=\"_blank\">{$c[1]}</a>";
		$r .= ($c[3] ? "（{$c[3]}）":'') . "</span></li>\n";
	}
	return $r.'</ul>';
}

function invite_state($invite) {
	if ('Y' == $invite['pay']) return '已返利';
	if ('C' == $invite['pay']) return '审核未通过';
	if ('N' == $invite['pay'] && $invite['buy_time']) return '待返利';
	if (time()-$invite['create_time']>7*86400) return '已过期';
	return '未购买';
}

function team_state(&$team) {
	if ( $team['now_number'] >= $team['min_number'] ) {
		if ($team['max_number']>0) {
			if ( $team['now_number']>=$team['max_number'] ){
				if ($team['close_time']==0) {
					$team['close_time'] = $team['end_time'];
				}
				return $team['state'] = 'soldout';
			}
		}
		if ( $team['end_time'] <= time() ) {
			$team['close_time'] = $team['end_time'];
		}
		return $team['state'] = 'success';
	} else {
		if ( $team['end_time'] <= time() ) {
			$team['close_time'] = $team['end_time'];
			return $team['state'] = 'failure';
		}
	}
	return $team['state'] = 'none';
}

function current_team($city_id=0) {
	$today = strtotime(date('Y-m-d'));
	$cond = array(
			'team_type' => 'normal',
			"begin_time <= {$today}",
			"end_time > {$today}",
			);
	/* 数据库匹配多个城市订单,前者按照多城市搜索,后者兼容旧字段city_id搜索 */
	$cond[] = "((city_ids like '%@{$city_id}@%' or city_ids like '%@0@%') or city_id in(0,{$city_id}))";
	$order = 'ORDER BY sort_order DESC, begin_time DESC, id DESC';
	/* normal team */
	$team = DB::LimitQuery('team', array(
				'condition' => $cond,
				'one' => true,
				'order' => $order,
				));
	if ($team) return $team;

	/* seconds team */
	$cond['team_type'] = 'seconds';
	unset($cond['begin_time']);	
	$order = 'ORDER BY sort_order DESC, begin_time ASC, id DESC';
	$team = DB::LimitQuery('team', array(
				'condition' => $cond,
				'one' => true,
				'order' => $order,
				));

	return $team;
}

function state_explain($team, $error='false') {
	$state = team_state($team);
	$state = strtolower($state);
	switch($state) {
		case 'none': return '正在进行中';
		case 'soldout': return '已售光';
		case 'failure': if($error) return '团购失败';
		case 'success': return '团购成功';
		default: return '已结束';
	}
}

function get_zones($zone=null) {
	$zones = array(
			'city' => '城市列表',
			'group' => '主题分类',
			'ticket'=> '门票分类',
			//'public' => '讨论区分类',
			//'grade' => '用户等级',
			//'express' => '快递公司',
			//'partner' => '商户分类',
			);
	if ( !$zone ) return $zones;
	if (!in_array($zone, array_keys($zones))) {
		$zone = 'city';
	}
	return array($zone, $zones[$zone]);
}

function mb_strimwidth_t($title,$start,$length,$ext)
{
    preg_match('/【(.*?)】(.*?)/',$title,$find);
    $title_mb = mb_strimwidth($title,$start,$length,$ext);
    $title_f = str_replace($find[0],'',$title_mb);
    return "<span class=\"xtitle\">".$find[0]."</span><span class=\"short-title\" style='color:#666'>".$title_f."</span>";
}


function down_xls($data, $keynames, $name='dataxls') {
	$xls[] = "<html><meta http-equiv=content-type content=\"text/html; charset=UTF-8\"><body><table border='1'>";
	$xls[] = "<tr><td>ID</td><td>" . implode("</td><td>", array_values($keynames)) . '</td></tr>';
	foreach($data As $o) {
		$line = array(++$index);
		foreach($keynames AS $k=>$v) {
			$line[] = $o[$k];
		}
		$xls[] = '<tr><td>'. implode("</td><td>", $line) . '</td></tr>';
	}
	$xls[] = '</table></body></html>';
	$xls = join("\r\n", $xls);
	header('Content-Disposition: attachment; filename="'.$name.'.xls"');
	die(mb_convert_encoding($xls,'UTF-8','UTF-8'));
}

function option_hotcategory($zone='city', $force=false, $all=false) {
	$cates = option_category($zone, $force, true);
	$r = array();
	foreach($cates AS $id=>$one) {
		if ('Y'==strtoupper($one['display'])) $r[$id] = $one;
	}
	return $all ? $r: Utility::OptionArray($r, 'id', 'name');
}

function option_category($zone='city', $force=false, $all=false) {
	$cache = $force ? 0 : 86400*30;
	$cates = DB::LimitQuery('category', array(
		'condition' => array( 'zone' => $zone, ),
		'order' => 'ORDER BY sort_order DESC, id DESC',
		'cache' => $cache,
	));
	$cates = Utility::AssColumn($cates, 'id');
	return $all ? $cates : Utility::OptionArray($cates, 'id', 'name');
}

function option_jingdian() {
    $jd = DB::LimitQuery('partner', array(
        'condition' => array( 'type' => 1, ),
        'order' => 'ORDER BY id DESC',
    ));
    //$cates = Utility::AssColumn($cates, 'id');
    return $jd;
}

function option_yes($n, $default=false) {
	global $INI;
	if (false==isset($INI['option'][$n])) return $default;
	$flag = trim(strval($INI['option'][$n]));
	return abs(intval($flag)) || strtoupper($flag) == 'Y';
}

function option_yesv($n, $default='N') {
	return option_yes($n, $default=='Y') ? 'Y' : 'N';
}

function magic_gpc($string) {
	if(SYS_MAGICGPC) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = magic_gpc($val);
			}
		} else {
			$string = stripslashes($string);
		}
	}
	return $string;
}

function team_discount($team, $save=false) {
	if ($team['market_price']<0 || $team['team_price']<0 ) {
		return '?';
	}
	return moneyit((10*$team['team_price']/$team['market_price']));
}

function ticket_discount($ticket)
{
    if ($ticket['t_price']<0 || $team['t_origin']<0 ) {
        return '?';
    }
    return moneyit((10*$ticket['t_price']/$ticket['t_origin']));
}

function team_origin($team, $quantity=0, $express_price = 0) {
	$origin = $quantity * $team['team_price'];
	if ($team['delivery'] == 'express'
			&& ($team['farefree']==0 || $quantity < $team['farefree'])
		) {
			$origin += $express_price;
		}
	return $origin;
}

function index_get_team($city_id,$group_id=0) {
	global $INI;
	$multi = option_yes('indexmulti');
	$city_id = abs(intval($city_id));

	/* 是否首页多团,不是则返回当前城市 */
	if (!$multi) return current_team($city_id);
	
	$now = time();
	$size = abs(intval($INI['system']['indexteam']));
	/* 侧栏团购数小于1,则返回当前城市数据 */
	if ($size<=1) return current_team($city_id);

	$oc = array( 
			'team_type' => 'normal',
			"begin_time < '{$now}'",
			"end_time > '{$now}'",
			);
    if($group_id) $oc['group_id']=$group_id;
	/* 数据库匹配多个城市订单,前者按照多城市搜索,后者兼容旧字段city_id搜索 */
	$oc[] = "(city_ids like '%@{$city_id}@%' or city_ids like '%@0@%') or (city_ids = '' and city_id in(0,{$city_id}))";
	$teams = DB::LimitQuery('team', array(
				'condition' => $oc,
				'order' => 'ORDER BY `sort_order` DESC, `id` DESC',
				));
	if(count($teams) == 1) return array_pop($teams);
	return $teams;
}

function error_handler($errno, $errstr, $errfile, $errline) {
	switch ($errno) {
		case E_PARSE:
		case E_ERROR:
			echo "<b>Fatal ERROR</b> [$errno] $errstr<br />\n";
			echo "Fatal error on line $errline in file $errfile";
			echo "PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
			exit(1);
			break;
		default: break;
	}
	return true;
}
/* for obscureid */
function obscure_rep($u) {
	if(!option_yes('encodeid')) return $u;
	if(preg_match('#/manage/#', $_SERVER['REQUEST_URI'])) return $u;
	return preg_replace_callback('#(\?|&)id=(\d+)(\b)#i', obscure_cb, $u);
}
function obscure_did() {
	$gid = strval($_GET['id']);
	if ($gid && preg_match('/^ZT/', $gid)) {
		$id = base64_decode(substr($gid,2))>>2;
		if($id) $_GET['id'] = $id;
	}
}
function obscure_cb($m) {
	$eid = obscure_eid($m[2]);
	return "{$m[1]}id={$eid}{$m[3]}";
}
function obscure_eid($id) {
	if($id>100000000) return $id;
	return 'ZT'.base64_encode($id<<2);
}
obscure_did();
/* end */

/* for post trim */
function trimarray($o) {
	if (!is_array($o)) return trim($o);
	foreach($o AS $k=>$v) { $o[$k] = trimarray($v); }
	return $o;
}
$_POST = trimarray($_POST);
/* end */

/* verifycapctch */
function verify_captcha($reason='none', $rurl=null) {
	if (option_yes($reason, false)) {
		$v = strval($_REQUEST['vcaptcha']);
		if(!$v || !Utility::CaptchaCheck($v)) {
			Session::Set('error', '验证码不匹配，请重新输入');
			redirect($rurl);
		}
	}
	return true;
}
function current_team_category($gid='0') {
    global $city;
    $city_id = $city['id'];
	$today = strtotime(date('Y-m-d'));
	$condition = array(
		'team_type' => 'normal',
		"begin_time <= '{$today}'",
		"end_time > '{$today}'",
	);
    $condition[] = "(city_ids like '%@{$city_id}@%' or city_ids like '%@0@%') or (city_ids = '' and city_id in(0,{$city_id}))";
	$categorys = DB::LimitQuery('category', array(
		'condition' => array( 'zone' => 'group','fid' => '0','display' => 'Y' ),
		'order' => 'ORDER BY sort_order DESC, id DESC',
	));
	$categorys = Utility::OptionArray($categorys, 'id', 'name');
	$a = array();
	foreach($categorys AS $id=>$name) {
	    $condition['group_id'] = $id;
	    $num= Table::Count('team', $condition);
		if(option_yes('rewritecity')){
			$a["/{$city['ename']}?gid=$id"] = $name.'('.$num.')';
		}else{
			$a["/index.php?gid=$id"] = $name.'('.$num.')';
		}
	}
	if(option_yes('rewritecity')){
		$l = "/{$city['ename']}?gid=$gid";
	}else{
		$l = "/index.php?gid=$gid";
	}	
	if (!$gid) $l = "/index.php";
	return current_link($l, $a, true);
}
/*add by wyy 2013-09-10*/
function getTicketType($tcate)
{
    $type = array(1=>'特价票',2=>'成人票',3=>'儿童票',4=>'套票',5=>'学生票');
    return $type[$tcate];
}

function GetMinPrice($partnerid)
{
    $tickets = DB::LimitQuery('ticket',
        array('condition'=>array('t_partner'=>$partnerid),'order'=>'order by t_price')
    );
    if(!$tickets)
    {
        return 0; //如果没有门票该景区 就显示0元
    }
    else
    {
        return floor($tickets[0]['t_price']);
    }
}
/*根据用户id 获取 用户名*/
function getUserById($userid)
{
    $user = Table::Fetch('user',$userid);
    //print_r($userid);
    return $user['username'];
}
function get_sms($order_id,$type)
{
    $fasong = false;
    $smses = array();
    $coupon = DB::LimitQuery('coupon',
            array('condition'=>array('order_id'=>$order_id),
                'one'=>true,
            )
        
    );
    //print_r($type);
    if($coupon[$type]>0)
    {
        return '已发';
    }
    if(!$fasong)
    {
        return '未发';
    }
}
//获取热门景点
function get_hot_scenery_ticket($city_id)
{
    if($city_id)
    {
        $sql = "select partner_id,count(*) as num from `order` where city_id=".$city_id." and type=1 group by partner_id order by num desc limit 0,8";
    }
    else
    {
        $sql ="select partner_id,count(*) as num from `order` where type=1 group by partner_id order by num desc limit 0,8";
    }
    
    $result = DB::Query($sql);
    $sellpartner = array();
    while($row = mysql_fetch_assoc($result))
    {
        $partner = Table::Fetch('partner',$row['partner_id']);
        $sellpartner[] = $partner;
    }
    $sellpartners= array();
    foreach($sellpartner as $key=>$p)
    {
        $ticketPrice = DB::LimitQuery('ticket', array(
                           'condition' => array( 
                            't_partner' => $p['id'],
                            't_status' =>1,
                            't_cate'=>2,
                            ) ,
                            //'size'=>8,
                            //'order'=>'ORDER BY id'
                            'one'=>true
                            ));
        if($ticketPrice['t_price'])
        {
            $p['ticketprice'] = $ticketPrice['t_price'];
            $p['t_origin'] = $ticketPrice['t_origin'];
        }
        else
        {
            $ticketPrice = DB::LimitQuery('ticket', array(
                           'condition' => array( 
                            't_partner' => $p['id'],
                            't_status' =>1,
                            ) ,
                            //'order'=>'ORDER BY id'
                            ));
            $p['ticketprice'] = $ticketPrice[0]['t_price'];
            $p['t_origin'] = $ticketPrice['t_origin'];
        }
        $sellpartners[] = $p;
    }
    return $sellpartners;
}


//获取热门乡村游
function get_hot_xcy_ticket($city_id)
{
    if($city_id)
    {
        $sql = "select partner_id,count(*) as num from `order` where city_id=".$city_id." and type=6 group by partner_id order by num desc limit 0,8";
    }
    else
    {
        $sql ="select partner_id,count(*) as num from `order` where type=6 group by partner_id order by num desc limit 0,8";
    }
    
    $result = DB::Query($sql);
    $sellpartner = array();
    while($row = mysql_fetch_assoc($result))
    {
        $partner = Table::Fetch('partner',$row['partner_id']);
        $sellpartner[] = $partner;
    }
    $sellpartners= array();
    foreach($sellpartner as $key=>$p)
    {
        $ticketPrice = DB::LimitQuery('ticket', array(
                           'condition' => array( 
                            't_partner' => $p['id'],
                            't_status' =>1,
                            't_cate'=>2,
                            ) ,
                            //'size'=>8,
                            //'order'=>'ORDER BY id'
                            'one'=>true
                            ));
        if($ticketPrice['t_price'])
        {
            $p['ticketprice'] = $ticketPrice['t_price'];
            $p['t_origin'] = $ticketPrice['t_origin'];
        }
        else
        {
            $ticketPrice = DB::LimitQuery('ticket', array(
                           'condition' => array( 
                            't_partner' => $p['id'],
                            't_status' =>1,
                            ) ,
                            //'order'=>'ORDER BY id'
                            ));
            $p['ticketprice'] = $ticketPrice[0]['t_price'];
            $p['t_origin'] = $ticketPrice['t_origin'];
        }
        $sellpartners[] = $p;
    }
    return $sellpartners;
}
/*门票折扣*/
function ticket_discount2($price1, $price2) {
    if ($price1<0 || $price2<0 ) {
        return '?';
    }
    return moneyit((10*$price1/$price2));
}
set_error_handler('error_handler');
