<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

need_manager();
$page = abs(intval($_GET['page']));
$pageweek = (7*$page); //上几周?
$thisday = date('Y-m-d');
$pageday  = date('Y-m-d',strtotime("-{$pageweek} days")); //向上回溯
$daytime = strtotime($pageday); $w = date('w'); //echo $w;
$fromtime = ($daytime-$w*86400); $endtime = ($daytime+(7-$w)*86400);

$team_count = Table::Count('team');

$todaytime = strtotime("-1day");
//echo $todaytime;
$contoday1 = "`create_time`>='{$todaytime}' AND `type`=1";
$contoday2 = "`create_time`>='{$todaytime}' AND `type`=2";
$contoday3 = "`create_time`>='{$todaytime}' AND `type`=3";
$contoday4 = "`create_time`>='{$todaytime}' AND `type`=4";

$contoday5 = "`create_time`>='{$todaytime}'";

$contoday6 = "`create_time`>='{$todaytime}'";

$conpro1 = "`type`=1";
$conpro2 = "`type`=2";
$conpro3 = "`type`=3";
$conpro4 = "`type`=5";

$ticket_order =Table::Count('order',$contoday1);
$zizhuyou_order =Table::Count('order',$contoday2);
$tuangou_order =Table::Count('order',$contoday3);
$dujiacun_order =Table::Count('order',$contoday4);
$usercount = Table::Count('user',$contoday5);
$partnercount = Table::Count('partner',$contoday6);

$ticket = Table::Count('order',$conpro1);
$zizhuyou = Table::Count('order',$conpro2);
$tuangou = Table::Count('order',$conpro3);
$dujiacun = Table::Count('order',$conpro4);

$order_count = Table::Count('order');
$user_count = Table::Count('user');
$partner_count = Table::Count('partner');

$subscribe_count = Table::Count('subscribe');
$condcreatetime = "`create_time`>='{$fromtime}' AND `create_time`<'{$endtime}'";
$condbegintime = "`begin_time`>='{$fromtime}' AND `begin_time`<'{$endtime}'";

$week = array(
    date('Y-m-d', $fromtime),
    date('Y-m-d', $fromtime+1*86400),
    date('Y-m-d', $fromtime+2*86400),
    date('Y-m-d', $fromtime+3*86400),
    date('Y-m-d', $fromtime+4*86400),
    date('Y-m-d', $fromtime+5*86400),
    date('Y-m-d', $fromtime+6*86400),
);

foreach($week AS $o)
{
    $t=strtotime($o);
    $c="begin_time<=$t AND end_time>=$t";
    $weekteamonline[$o]=Table::Count('team',$c);
}
$weekuser = Table::Group('user', $condcreatetime, 'LEFT(FROM_UNIXTIME(create_time),10)');
$weekteamnew = Table::Group('team', $condbegintime, 'LEFT(FROM_UNIXTIME(begin_time),10)');
$condorderpay = array($condcreatetime, 'state'=>'pay');
$weekorderpay = Table::Group('order', $condorderpay, 'LEFT(FROM_UNIXTIME(create_time),10)');
$weekorderpayorigin = Table::Group('order', $condorderpay, 'LEFT(FROM_UNIXTIME(create_time),10)', 'origin');
$weekorderpaycredit = Table::Group('order', $condorderpay, 'LEFT(FROM_UNIXTIME(create_time),10)', 'credit');
$weekorderpaymoney = Table::Group('order', $condorderpay, 'LEFT(FROM_UNIXTIME(create_time),10)', 'money');
$condorderunpay = array($condcreatetime, 'state'=>'unpay');
$weekorderunpay = Table::Group('order', $condorderunpay, 'LEFT(FROM_UNIXTIME(create_time),10)');
$condflowcharge = array($condcreatetime, 'action'=>'charge');
$weekflowcharge = Table::Group('flow', $condflowcharge, 'LEFT(FROM_UNIXTIME(create_time),10)', 'money');
$condflowstore = array($condcreatetime, 'action'=>'store');
$weekflowstore = Table::Group('flow', $condflowstore, 'LEFT(FROM_UNIXTIME(create_time),10)', 'money');
$condflowwithdraw = array($condcreatetime, 'action'=>'withdraw');
$weekflowwithdraw = Table::Group('flow', $condflowwithdraw, 'LEFT(FROM_UNIXTIME(create_time),10)', 'money');
$version = strval(SYS_VERSION);
$subversion = strval(SYS_SUBVERSION);
$action = strval($_GET['action']);

$version_meta = zuitu_version($version);
$newversion = $version_meta['version'];
$newsubversion = $version_meta['subversion'];
$software = $version_meta['software'];
$isnew = ( $newversion==$version && $subversion == $newsubversion ) ;

if ( 'db' == $action ) {
	$r = zuitu_upgrade($action, $version);
	log_admin('misc', '升级数据库结构');
	Session::Set('notice', '数据库结构升级成功，数据库已经是最新版本');
	redirect( WEB_ROOT . '/manage/misc/index.php' );
}
else if ( 'opt' == $action ) {
	$tables = DB::GetQueryResult("SHOW TABLE STATUS", false);
	foreach($tables AS $one) {
		DB::Query("OPTIMIZE TABLE {$one['name']}");
	}
    log_admin('misc', '数据库结构优化');
	Session::Set('notice', '数据库结构优化完成');
	redirect( WEB_ROOT . '/manage/misc/index.php' );
}

include template('manage_misc_index');
function sum($a=array(), $k=null) {
    $r=0; foreach($a AS $i=>$v) $r+=$v; return $r;
}
