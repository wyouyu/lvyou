<?php
/**
 * @author: shwdai@gmail.com
 */
class FalseCache{public function __call($m, $v){}};
class Cache
{

	static private $mCache = null;
	static private $gCache = array();
	static private $mInstance = null;
	static private $mIndex = 0;

	static public function Instance()
	{
		if ( !self::$mInstance ) self::$mInstance = new Cache();
	}

	static private function _CreateCacheInstance()
	{
		global $INI;
		$ini = Config::Instance('php'); settype($ini['memcache'],'array');
		if (!class_exists('Memcache', false)) return new FalseCache();
		$cache_instance = new Memcache();
		foreach( $INI['memcache'] AS $one )
		{
			$server =  (string) $one;
			list($ip, $port, $weight) = explode(':', $server);
			if(!$ip || !$port || !$weight) continue;
			$cache_instance->addServer( $ip
					,$port
					,true
					,$weight
					,1
					,15
					,true
					,array('Cache','FailureCallback')
					);
			self::$mIndex++;
		}
		return self::$mIndex ? $cache_instance : new FalseCache();
	}

	private function __construct()
	{
		self::$mCache = self::_CreateCacheInstance();
	}

	static public function FailureCallback($ip, $port)
	{
		self::$mIndex--; if (self::$mIndex<=0) self::$mCache = new FalseCache();
	}

	static function Get($key) 
	{
		self::Instance();
		if (is_array($key)) {
			$v = array();
			foreach($key as $k) {
				$vv = self::Get($k);
				if ($vv) { 
					$v[$k] = $vv; 
				}
			}
			return $v;
		} else {
			if(isset(self::$gCache[$key])) { 
				return self::$gCache[$key];
			}
			$v = self::$mCache->get($key);
			if ($v) { self::$gCache[$key] = $v; }
			return $v;
		}
	}


	static function Add($key, $var, $flag=0, $expire=0) {
		self::Instance();
		self::$mCache->add($key,$var,$flag,$expire);
		self::$gCache[$key] = $var;
	}


	static function Dec($key, $value=1)
	{
		self::Instance();
		return self::$mCache->decrement($key, $value);
	}


	static function Inc($key, $value=1)
	{
		self::Instance();
		return self::$mCache->increment($key, $value);
	}

	static function Replace($key, $var, $flag=0, $expire=0)
	{
		self::Instance();
		return self::$mCache->replace($key, $var, $flag, $expire);
	}


	static function Set($key, $var, $flag=0, $expire=0) {
		self::Instance();
		self::$mCache->set($key, $var, $flag, $expire);
		self::$gCache[$key] = $var;
		return true;
	}

	static function Del($key, $timeout=0) {
		self::Instance();
		if (is_array($key)) {
			foreach ($key as $k) { 
				self::$mCache->delete($k, $timeout);
				if (isset(self::$gCache[$k])) unset(self::$gCache[$k]);
			}
		} else {
			self::$mCache->delete($key, $timeout);
			if (isset(self::$gCache[$key])) unset(self::$gCache[$k]);
		}
		return true;
	}

	static function Flush()
	{
		self::Instance();
		return self::$mCache->flush();
	}

	static function GetFunctionKey($callback, $args=array())
	{
		$args = ksort($args);
		$patt = "/(=>)\s*'(\d+)'/";
		$args_string = var_export($args, true);
		$args_string = preg_replace($patt, "\\1\\2", $args_string);
		$key = "[FUNC]:$callback($args_string)";
		return self::GenKey( $key );
	}

	static function GetStringKey($str=null) {
		settype($str, 'array'); $str = var_export($str,true);
		$key = "[STR]:{$str}";
		return self::GenKey( $key );
	}

	static function GetObjectKey($tablename, $id)
	{
		$key = "[OBJ]:$tablename($id)";
		return self::GenKey( $key );
	}

	static function GenKey($key) {
		$hash = dirname(__FILE__);
		return md5( $hash . $key );
	}

	static function SetObject($tablename, $one) {
		self::Instance();
		foreach($one AS $oone) {
			$k = self::GetObjectKey($tablename, $oone['id']);
			self::Set($k, $oone);
		}
		return true;
	}

	static function GetObject($tablename, $id) {
		$single = ! is_array($id);
		settype($id, 'array');
		$k = array();
		foreach($id AS $oid) {
			$k[] = self::GetObjectKey($tablename, $oid);
		}
		$r = Utility::AssColumn(self::Get($k), 'id');
		return $single ? array_pop($r) : $r;
	}

	static function ClearObject($tablename, $id) {
		settype($id, 'array');
		foreach($id AS $oid) {
			$key = self::GetObjectKey($tablename, $oid);
			self::Del($key);
		}
		return true;
	}
}
?>
