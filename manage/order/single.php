<?php
/*
 * @FileName : single.php 
 * @Description : 支付宝单笔查询
 * @para:
 * @return : 
 * */

require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

if( is_get() )
{
	
	$no = trim(strval($_GET['no'])) ;
	if( !$no )
	{
		include template( 'manage_order_single' );
		die(0);
	}	
	
	$partner = $INI['alipay']['mid'];
	$security_code = $INI['alipay']['sec'];
	$seller_email = $INI['alipay']['acc'];
	//禁止支付宝账号为空
	if( !$partner || !$security_code || !$seller_email )
	{
		die('fail');
	}

	//参数设置
	$_input_charset = "utf-8" ;		//设置编码方式
	$sign_type = "MD5" ;
	$transport = 'http' ;

	$parameter = array(
		"service"         => "single_trade_query", //交易类型
		"partner"         => $partner,             //合作商户号
		"_input_charset"  => $_input_charset,      //字符集，默认为GBK
		"out_trade_no"    => "$no",     //支付宝交易号
	);

	//支付宝类初始化参数、建立单条查询链接参数
	$alipay = new AlipayService( $parameter , $security_code , $sign_type , $transport );
	$xml_file=$alipay->create_url();

	//XML 文件解析
	$xml_parser = xml_parser_create();	//建立XML解析对象
	xml_set_element_handler( $xml_parser , 'startElement' , 'endElement' );
	xml_set_character_data_handler( $xml_parser , 'parseData' ) ;
	
	if( !$fp = fopen( $xml_file , 'r' ))	die( 'could not open xml file .' );
	
	while( $data = fread( $fp , 4096 ) )
	{
		if( !xml_parse( $xml_parser , $data , feof($fp) ) )
		{
			var_dump($data);
			die( sprintf( 'XML error : %s at line $d ' , xml_error_string(xml_get_error_code($xml_parser)) , xml_get_current_line_number($xml_parser) ) );
		}
	}
	
	xml_parser_free( $xml_parser );		//释放XML解析对象
	
}

include template( 'manage_order_single' );

//起始处理函器
function startElement( $parser , $name , $data )
{
	global $alipayInfo ;
	switch( $name )
	{
		case 'ALIPAY' : 	
				$alipayInfo .= '--支付宝返回数据--<br />';
				break;
		case 'IS_SUCCESS' :
				$alipayInfo .= '是否成功 : ';
				break;	
		case 'RESPONSE' : 
				$alipayInfo .= '返回输出 : ' ;
				break;
		case 'TRADE_NO' : 
				$alipayInfo .= '支付宝交易号 : ' ;
				break;
		case 'OUT_TRADE_NO' : 
				$alipayInfo .= '外部交易号 : ' ;
				break;
		case 'SUBJECT' : 
				$alipayInfo .= '商品名称 : ' ;
				break;
		case 'BODY' : 
				$alipay .= '商品描述 : ' ;
				break;
		case 'PRICE' : 
				$alipayInfo .= '商品单价 : ' ;
				break;
		case 'DISCOUNT' : 
				$alipayInfo .= '折扣 : ' ;
				break;
		case 'QUANTITY' : 
				$alipayInfo .= '购买数量 : ' ;
				break;
		case 'TOTAL_FEE' : 
				$alipayInfo .= '总交易金额 : ' ;
				break;
		case 'LOGISTICS_FEE' : 
				$alipayInfo .= '邮费 : ' ;
				break;
		case 'PAYMENT_TYPE' : 
				$alipayInfo .= '支付类型 : ' ;
				break;
		case 'USE_COUPON' : 
				$alipayInfo .= '是否使用红包 : ' ;
				break;
		case 'COUPON_DISCOUNT' : 
				$alipayInfo .= '红包折扣 : ' ;
				break;
		case 'IS_TOTAL_FEE_ADJUST' : 
				$alipayInfo .= '交易金额是否修改过 : ' ;
				break;
		case 'TRADE_STATUS' : 
				$alipayInfo .= '交易状态 : ' ;
				break;
		case 'REFUND_STATUS' : 
				$alipayInfo .= '退款状态 : ' ;
				break;
		case 'LOGISTICS_STATUS' : 
				$alipayInfo .= '物流状态 : ' ;
				break;
		case 'GMT_CREATE' : 
				$alipayInfo .= '交易创建时间 : ' ;
				break;
		case 'GMT_PAYMENT' : 
				$alipayInfo .= '买家付款时间 : ' ;
				break;
		case 'GMT_SEND_GOODS' : 
				$alipayInfo .= '买家发货时间 : ' ;
				break;
		case 'GMT_REFUND' : 
				$alipayInfo .= '退款时间 : ' ;
				break;
		case 'GMT_CLOSE' :
				$alipayInfo .= '交易结束时间 : ';
				break;	
		case 'GMT_LOGISTICS_MODIFY' :
				$alipayInfo .= '物流状态更新时间 : ';	
				break;
		case 'GMT_LAST_MODIFIED_TIME' :
				$alipayInfo .= '最后修改时间 : ';
				break;	
		case 'SELLER_EMAIL' :
				$alipayInfo .= '卖家Email账号 : ';
				break;	
		case 'BUYER_EMAILL' :
				$alipayInfo .= '买家Email账号 : ';	
				break;
		case 'SELLER_ID' :
				$alipayInfo .= '卖家userId : ';	
				break;
		case 'BUYER_ID' :
				$alipayInfo .= '买家userId:';	
				break;
		case 'ADDITIONAL_TRADE_STATUS' :
				$alipayInfo .= '交易附加状态 : ';
				break;	
		case 'FLAG_TRADE_LOCKED' :
				$alipayInfo .= '交易冻结状态 : ';
				break;	
		case 'TIME_OUT_TYPE' :
				$alipayInfo .= '主超时类型 : ';	
				break;
		case 'TIME_OUT' :
				$alipayInfo .= '主超时时间 : ';	
				break;
		case 'REFUND_FEE' :
				$alipayInfo .= '退款金额 : ';
				break;	
		case 'REFUND_FLOW_TYPE' :
				$alipayInfo .= '退款流程 : ';
				break;	
		case 'REFUND_ID' :
				$alipayInfo .= '退款I : ';
				break;	
		case 'REFUND_CASH_FEE' :
				$alipayInfo .= '退现金金额 : ';
				break;	
		case 'REFUND_COUPON_FEE' :
				$alipayInfo .= '退红包金额 : ';	
				break;
		case 'REFUND_AGENT_PAY_FEE' :
				$alipayInfo .= '退积分金额 : ';	
				break;																																																																																																				
		case 'ERROR':
				$alipayInfo .= '错误 : ';
				break;
		default : 
				$alipayInfo .= "{$name} : " ;
	}
}
	
//结束处理函器
function endElement ( $parser , $name )
{
	global $alipayInfo;
	$alipayInfo .= '<br />';
}
	
//数据解析函器
function parseData( $parser , $data )
{
	global $alipayInfo;
	$alipayInfo .= $data ;
}
