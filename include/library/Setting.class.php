<?php
/**
 * @author: shwdai@gmail.com
 */
class Setting
{
	static function GetTag($tagname='dochead', $ass=null) {
		$xml = Config::Instance('xml');
		$r = array();
		if(!$xml->$tagname->item) return $r;
		foreach ($xml->$tagname->item AS $one) {
			$attr = $one->attributes();
			$pa = array();
			foreach($attr AS $k=>$v) {
				$pa[strval($k)] = strval($v);
			}
			$r[] = $pa;
		}
		if ($ass)  return Utility::AssColumn($r, 'id');
		return $r;
	}
}
