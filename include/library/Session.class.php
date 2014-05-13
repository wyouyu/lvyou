<?php
/**
 * @author: shwdai@gmail.com
 */
class Session
{
	static private $_begin = 0;
	static private $_instance = null;
	static private $_debug = false;

	static public function Init($debug=false)
	{
		self::$_instance = new Session();
		self::$_debug = $debug;
		session_start();
	}

	static public function Set($name, $v) 
	{
		$_SESSION[$name] = $v;
	}

	static public function Get($name, $once=false)
	{
		$v = null;
		if ( isset($_SESSION[$name]) )
		{
			$v = $_SESSION[$name];
			if ( $once ) unset( $_SESSION[$name] );
		}
		return $v;
	}

	function __construct()
	{
		self::$_begin = microtime(true);
	}

	function __destruct()
	{
		global $AJAX, $INI;
		if (self::$_debug&&!$AJAX) { echo 'Generation Cost: '.(microtime(true)-self::$_begin).'s, Query Count: ' . DB::$mCount; }
		DB::Close();
		$c = ob_get_clean();
		if ( function_exists('render_hook') ) {
			$c = render_hook($c);
		}
		if ( function_exists('output_hook') ) {
			die(output_hook($c));
		}
		die($c);
	}
}
?>
