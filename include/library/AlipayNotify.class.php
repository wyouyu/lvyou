<?php
class AlipayNotify {

	var $gateway;           //支付接口
	var $security_code;  	//安全校验码
	var $partner;           //合作伙伴ID
	var $sign_type;         //加密方式 系统默认
	var $mysign;            //签名     
	var $_input_charset;    //字符编码格式
	var $transport;         //访问模式

	function AlipayNotify($partner,$security_code,$sign_type = "MD5",$_input_charset = "GBK",$transport= "http") {
		$this->partner        = $partner;
		$this->security_code  = $security_code;
		$this->sign_type      = $sign_type;
		$this->mysign         = "";
		$this->_input_charset = $_input_charset ;
		$this->transport      = $transport;
		$this->gateway = "http://notify.alipay.com/trade/notify_query.do?";
	}

	function notify_verify() {
		
		$veryfy_url = $this->gateway. "partner=".$this->partner."&notify_id=".$_POST["notify_id"];
		
		$veryfy_result = $this->get_verify($veryfy_url);
		$post          = $this->para_filter($_POST);
		$sort_post     = $this->arg_sort($post);
		while (list ($key, $val) = each ($sort_post)) {
			$arg.=$key."=".$val."&";
		}
		
		$prestr = rtrim($arg, '&');
		$this->mysign = $this->sign($prestr.$this->security_code);
		//file_put_contents('a.txt',$veryfy_result);
		if (eregi("true$",$veryfy_result) 
			&& $this->mysign == $_POST["sign"]){
			return true;
		} else return false;
	}

	function return_verify() {  
		$veryfy_url = $this->gateway. "partner=".$this->partner."&notify_id=".$_GET["notify_id"];
		$veryfy_result = $this->get_verify($veryfy_url);
		$get = $this->para_filter($_GET);
		$sort_get = $this->arg_sort($get);
		while (list ($key, $val) = each ($sort_get)) {
			$arg.=$key."=".$val."&";
		}
		$prestr = rtrim($arg, '&');
		$this->mysign = $this->sign($prestr.$this->security_code);
		if (eregi("true$",$veryfy_result) 
				&& $this->mysign == $_GET["sign"]) {
			return true;
		}
		else return false;
	}
	/*******************************************************************************************/

	function get_verify($url,$time_out = "60") {
		return Utility::HttpRequest($url);
	}

	function arg_sort($array) {
		ksort($array);
		reset($array);
		return $array;
	}

	function sign($prestr) {
		$sign='';
		if($this->sign_type == 'MD5') {
			$sign = md5($prestr);
		}elseif($this->sign_type =='DSA') {
			//DSA 签名方法待后续开发
			die("DSA 签名方法待后续开发，请先使用MD5签名方式");
		}else {
			die("支付宝暂不支持".$this->sign_type."类型的签名方式");
		}
		return $sign;
	}
	/***********************除去数组中的空值和签名模式*****************************/
	function para_filter($parameter) { 
		$para = array();
		while (list ($key, $val) = each ($parameter)) {
			if($key == "sign" || $key == "sign_type" || $val == "")continue;
			else	$para[$key] = $parameter[$key];
		}
		return $para;
	}

	function charset_encode($input,$_output_charset ,$_input_charset ="utf-8" ) {
		$output = "";
		if(!isset($_output_charset) )$_output_charset  = $this->parameter['_input_charset'];
		if($_input_charset == $_output_charset || $input ==null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")){
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset change.");
		return $output;
	}

	function charset_decode($input,$_input_charset ,$_output_charset="utf-8"  ) {
		$output = "";
		if(!isset($_input_charset) )$_input_charset  = $this->_input_charset ;
		if($_input_charset == $_output_charset || $input ==null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")){
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset changes.");
		return $output;
	}
}
