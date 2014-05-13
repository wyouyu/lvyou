<?php
class ZSystem
{
	static public function GetINI() {
		global $INI;
		/* load from php*/
		$dbphp = DIR_CONFIGURE . '/db.php';
		if (file_exists($dbphp)) {
			configure_load();
		} else {
			/* end */
			$INI = Config::Instance('php');
			$SYS = Table::Fetch('system', 1);
			$SYS = Utility::ExtraDecode($SYS['value']);
			$INI = Config::MergeINI($INI, $SYS);
		}
		$INI = ZSystem::WebRoot();
		return self::BuildINI($INI);
	}

	static public function GetUnsetINI($ini) {
		if (false==function_exists('configure_save')) {
			unset($ini['db']);
			unset($ini['webroot']);
			unset($ini['memcache']);
		}
		return $ini;
	}

	static public function GetSaveINI($ini) {
		return array(
				'db' => $ini['db'],
				'memcache' => $ini['memcache'],
				'webroot' => $ini['webroot'],
				);
	}

	static private function WebRoot() {
		global $INI; if (defined('WEB_ROOT')) return $INI;
		/* validator */
		$script_name = $_SERVER['SCRIPT_NAME'];
		if ( preg_match('#^(.*)/app.php$#', $script_name, $m) ) {
			$INI['webroot'] = $m[1];
			save_config('php');
		}

		if (isset($INI['webroot'])) {
			define('WEB_ROOT', $INI['webroot']);
		} else {
			$document_root = $_SERVER['DOCUMENT_ROOT'];
			$docroot = rtrim(str_replace('\\','/',$document_root),'/');
			if(!$docroot) {
				$script_filename = $_SERVER['SCRIPT_FILENAME'];
				$script_filename = str_replace('\\','/',$script_filename);
				$script_name = $_SERVER['SCRIPT_NAME'];
				$script_name = str_replace('\\','/',$script_name);
				$lengthf = strlen($script_filename);
				$lengthn = strlen($script_name);
				$length = $lengthf - $lengthn;
				$docroot = rtrim(substr($script_filename,0,$length),'/');
			}
			$webroot = trim(substr(WWW_ROOT, strlen($docroot)), '\\/');
			define('WEB_ROOT', $webroot ? "/{$webroot}" : '');
		}
        //print_r($INI);
		return $INI;
	}

	static private function BuildINI($ini) {
		$host = $_SERVER['HTTP_HOST'];
		if(!$ini['system']['wwwprefix']) {
			$ini['system']['wwwprefix'] = "http://{$host}" . WEB_ROOT;
		}
		if(!$ini['system']['imgprefix']) {
			$ini['system']['imgprefix'] = "http://{$host}" . WEB_ROOT;
		}
		if(!$ini['system']['cssprefix']) {
			$ini['system']['cssprefix'] = "http://{$host}" . WEB_ROOT;
		}
		if(!$ini['system']['sitename']) {
			$ini['system']['sitename'] = '最土网';
		}
		if(!$ini['system']['sitetitle']) {
			$ini['system']['sitetitle'] = '精品团购每一天';
		}
		if(!$ini['system']['abbreviation']) {
			$ini['system']['abbreviation'] = '最土';
		}
		if(!$ini['system']['couponname']) {
			$ini['system']['couponname'] = '优惠券';
		}
		if(!$ini['system']['currency']) {
			$ini['system']['currency'] = '&#165;';
		}
		if(!$ini['system']['currencyname']) {
			$ini['system']['currencyname'] = 'CNY';
		}
		if(!$ini['mail']['encoding']) {
			$ini['mail']['encoding'] = 'UTF-8';
		}
		if(!$ini['system']['timezone']) {
			$ini['system']['timezone'] = 'Etc/GMT-8';
		}
		return $ini;
	}

	static public function GetThemeList() {
		$root = WWW_ROOT . '/static/theme';
		$handle = opendir($root);
		$themelist = array( 'default'=> 'default',);
		while($one = readdir($handle)) {
			if ( strpos($one,'.') === 0 ) continue;
			$onedir = $root . '/' . $one;
			if ( is_dir($onedir ) ) $themelist[$one] = $one;
		}
		return $themelist;
	}

	static public function GetTemplateList() {
		$root = DIR_TEMPLATE;
		$handle = opendir($root);
		$templatelist = array( 'default'=> 'default',);
		while($one = readdir($handle)) {
			if ( strpos($one,'.') === 0 ) continue;
			$onedir = $root . '/' . $one;
			if ( is_dir($onedir ) ) $templatelist[$one] = $one;
		}
		return $templatelist;
	}
}
